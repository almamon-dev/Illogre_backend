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

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $host = env('IMAP_HOST');
        $username = env('IMAP_USERNAME');
        $password = env('IMAP_PASSWORD');

        if (!$host || !$username || !$password) {
            $this->error("IMAP configuration is incomplete in .env file.");
            return Command::FAILURE;
        }

        $this->info("Connecting to IMAP host: {$host} as {$username}...");

        try {
            $cm = new ClientManager();
            $client = $cm->make([
                'host'          => $host,
                'port'          => env('IMAP_PORT', 993),
                'encryption'    => env('IMAP_ENCRYPTION', 'ssl'),
                'validate_cert' => true,
                'username'      => $username,
                'password'      => $password,
                'protocol'      => 'imap'
            ]);

            $client->connect();
            $this->info("Connected successfully!");

            // Get INBOX folder
            $this->info("Accessing INBOX folder...");
            $folder = $client->getFolder('INBOX');
            
            // Fetch the latest 10 messages directly from INBOX (avoids slow search queries)
            $this->info("Fetching the latest 10 messages from INBOX...");
            $messages = $folder->query()
                ->leaveUnread()
                ->setFetchOrder('desc')
                ->limit(10)
                ->get();
            
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
                $toEmail = !empty($toArray) ? $toArray[0]->mail : $username;

                // Get content body (prefer HTML, fallback to text)
                $body = $message->hasHTMLBody() ? $message->getHTMLBody() : $message->getTextBody();
                
                // Process ticket generation
                $this->processEmailAsTicket($fromEmail, $fromName, $toEmail, $subject, $body);

                // Mark the message as read (Seen)
                $message->setFlag('Seen');
            }

            $this->info("Processed {$processedCount} new unread emails.");

            $client->disconnect();
            $this->info("Finished processing emails.");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Error fetching emails: " . $e->getMessage());
            Log::error("FetchEmails command error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Process the email content to find/create customer and create a ticket
     */
    private function processEmailAsTicket($fromEmail, $fromName, $toEmail, $subject, $body)
    {
        Log::info("FetchEmails: Processing message from {$fromEmail} to {$toEmail}");

        // 1. Identify Owner
        $ownerId = null;
        if ($toEmail) {
            $setting = UserSetting::where('key', 'support_email')
                ->where('value', $toEmail)
                ->first();
            if ($setting) {
                $ownerId = $setting->user_id;
            }
        }

        // Fallback: match by sender's customer record owner
        if (!$ownerId) {
            $customer = Customer::where('email', $fromEmail)->first();
            if ($customer) {
                $ownerId = $customer->owner_id;
            }
        }

        // Final Fallback: use first owner user
        if (!$ownerId) {
            $ownerId = User::where('user_type', 'owner')->first()?->id;
        }

        // 2. Find or create Customer
        $customer = Customer::where('email', $fromEmail)
            ->where('owner_id', $ownerId)
            ->first();

        if (!$customer) {
            Log::info("FetchEmails: Customer not found. Auto-creating customer: {$fromName}");
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
