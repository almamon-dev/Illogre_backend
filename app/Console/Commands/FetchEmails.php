<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Webklex\PHPIMAP\ClientManager;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Support\Facades\Log;

class FetchEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch unread emails from configured IMAP account and create support tickets';

    public function handle()
    {
        $accounts = [];

        // 1. Load from database integrations (Multi-tenant)
        try {
            $dbIntegrations = \App\Models\Integration::whereIn('provider', ['gmail', 'outlook'])
                ->where('status', 'connected')
                ->get();

            foreach ($dbIntegrations as $integration) {
                $settings = $integration->settings ?? [];
                
                $defaultHost = $integration->provider === 'gmail' ? 'imap.gmail.com' : 'outlook.office365.com';
                $password = $settings['imap_password'] ?? null;

                if (!$password) {
                    continue;
                }

                $accounts[] = [
                    'host'       => $settings['imap_host'] ?? $defaultHost,
                    'port'       => $settings['imap_port'] ?? 993,
                    'encryption' => $settings['imap_encryption'] ?? 'ssl',
                    'username'   => $integration->provider_id, // Connected email address
                    'password'   => $password,
                    'owner_id'   => $integration->user_id,
                    'source'     => 'database_integration'
                ];
            }
        } catch (\Exception $e) {
            $this->error("Failed to load integrations from database: " . $e->getMessage());
            Log::error("FetchEmails: DB integration load failed: " . $e->getMessage());
        }

        if (empty($accounts)) {
            $this->info("No IMAP accounts configured (either in .env or database).");
            return Command::SUCCESS;
        }

        $cm = new ClientManager();

        foreach ($accounts as $account) {
            $this->info("--------------------------------------------------");
            $this->info("Processing account: {$account['username']} (Source: {$account['source']}, Owner ID: {$account['owner_id']})");

            try {
                $client = $cm->make([
                    'host'          => $account['host'],
                    'port'          => $account['port'],
                    'encryption'    => $account['encryption'],
                    'validate_cert' => true,
                    'username'      => $account['username'],
                    'password'      => $account['password'],
                    'protocol'      => 'imap'
                ]);

                $client->connect();
                $this->info("Connected successfully!");

                // Get INBOX folder
                $this->info("Accessing INBOX folder...");
                $folder = $client->getFolder('INBOX');
                
                // Get total messages count in INBOX
                $info = $folder->examine();
                $totalMessages = $info['exists'] ?? 0;
                $this->info("Total messages in INBOX: {$totalMessages}");

                // Paginate to retrieve the newest messages (avoids slow/unsupported IMAP SORT command)
                $perPage = 50;
                $lastPage = $totalMessages > 0 ? (int)ceil($totalMessages / $perPage) : 1;
                
                $this->info("Fetching the latest messages (page {$lastPage})...");
                $messages = $folder->query()
                    ->leaveUnread()
                    ->all()
                    ->limit($perPage, $lastPage)
                    ->get();
                
                // If the last page has very few messages, merge with the previous page for safety
                if ($messages->count() < 15 && $lastPage > 1) {
                    $this->info("Last page had few messages, merging with the previous page...");
                    $prevMessages = $folder->query()
                        ->leaveUnread()
                        ->all()
                        ->limit($perPage, $lastPage - 1)
                        ->get();
                    $messages = $messages->merge($prevMessages);
                }
                
                $count = $messages->count();
                $this->info("Retrieved {$count} messages. Checking for unread (unseen) status...");

                $processedCount = 0;
                foreach ($messages as $message) {
                    // If the message has already been Seen (read), skip it
                    if ($message->getFlags()->has('Seen')) {
                        continue;
                    }

                    $processedCount++;
                    $subject = $message->getSubject() ?? 'No Subject';
                    $fromArray = $message->getFrom();
                    
                    if (empty($fromArray)) {
                        $this->warn("Skipping email: Sender email missing.");
                        continue;
                    }
                    
                    $fromEmail = $fromArray[0]->mail;
                    $fromName = $fromArray[0]->personal ?? explode('@', $fromEmail)[0];
                    
                    $toArray = $message->getTo();
                    $toEmail = !empty($toArray) ? $toArray[0]->mail : $account['username'];

                    // Get content body (prefer HTML, fallback to text)
                    $body = $message->hasHTMLBody() ? $message->getHTMLBody() : $message->getTextBody();
                    
                    // Process ticket generation
                    $this->processEmailAsTicket($fromEmail, $fromName, $toEmail, $subject, $body, $account['owner_id']);

                    // Mark the message as read (Seen)
                    $message->setFlag('Seen');
                }

                $this->info("Processed {$processedCount} new unread emails for {$account['username']}.");
                $client->disconnect();

            } catch (\Exception $e) {
                $this->error("Error fetching emails for {$account['username']}: " . $e->getMessage());
                Log::error("FetchEmails command error for {$account['username']}: " . $e->getMessage());
            }
        }

        $this->info("Finished processing all email accounts.");
        return Command::SUCCESS;
    }

    /**
     * Process the email content to find/create customer and create a ticket
     */
    private function processEmailAsTicket($fromEmail, $fromName, $toEmail, $subject, $body, $ownerId)
    {
        Log::info("FetchEmails: Processing message from {$fromEmail} to {$toEmail} for owner {$ownerId}");

        // If no owner ID was determined, try to find one
        if (!$ownerId) {
            if ($toEmail) {
                $setting = UserSetting::where('key', 'support_email')
                    ->where('value', $toEmail)
                    ->first();
                if ($setting) {
                    $ownerId = $setting->user_id;
                }
            }
            
            if (!$ownerId) {
                $customer = Customer::where('email', $fromEmail)->first();
                if ($customer) {
                    $ownerId = $customer->owner_id;
                }
            }

            if (!$ownerId) {
                $ownerId = User::where('user_type', 'owner')->first()?->id;
            }
        }

        // 2. Find or create Customer
        $customer = Customer::where('email', $fromEmail)
            ->where('owner_id', $ownerId)
            ->first();

        if (!$customer) {
            Log::info("FetchEmails: Customer not found. Auto-creating customer: {$fromName} for owner: {$ownerId}");
            $customerName = ucfirst(str_replace(['.', '_', '-'], ' ', $fromName));
            
            $customer = Customer::create([
                'email' => $fromEmail,
                'name' => $customerName,
                'owner_id' => $ownerId,
                'status' => 'New',
            ]);
        }

        // 3. Load recent orders for context
        $recentOrders = Order::where('customer_id', $customer->id)
            ->where('owner_id', $ownerId)
            ->orderBy('shopify_created_at', 'desc')
            ->limit(3)
            ->get();

        // 4. Create the Ticket
        $ticket = Ticket::create([
            'ticket_number' => 'TIC-' . strtoupper(uniqid()),
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_id' => $customer->id,
            'subject' => $subject,
            'body' => strip_tags($body), // clean up html tags for database body
            'category' => 'Inquiry',
            'source' => 'Email',
            'confidence' => $recentOrders && $recentOrders->count() > 0 ? 90 : 40,
            'status' => 'Pending',
            'assigned' => 'AI Agent',
            'owner_id' => $ownerId,
        ]);

        $this->info("Ticket created: {$ticket->ticket_number} for customer: {$customer->name}");
        Log::info("FetchEmails: Ticket created: {$ticket->ticket_number} for customer: {$customer->name}");
    }
}
