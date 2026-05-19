<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Support\Facades\Log;

class InboundEmailController extends Controller
{
    /**
     * Handle incoming email webhooks (e.g. from Postmark or SendGrid)
     */
    public function handle(Request $request)
    {
        $payload = $request->all();
        
        // Example fields (mapping depends on provider)
        $fromEmail = $payload['from'] ?? $payload['From'] ?? null;
        $toEmail = $payload['to'] ?? $payload['To'] ?? null;
        $subject = $payload['subject'] ?? $payload['Subject'] ?? 'No Subject';
        $body = $payload['body'] ?? $payload['TextBody'] ?? '';
        $source = $payload['source'] ?? $payload['channel'] ?? $payload['Source'] ?? 'Email';

        if (!$fromEmail) {
            return response()->json(['error' => 'Sender email missing'], 400);
        }

        Log::info("Inbound Message Received: From {$fromEmail} to {$toEmail} via {$source}");

        // 1. Identify the Owner (Business) based on support_email setting
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

        // Final Fallback: use first owner
        if (!$ownerId) {
            $ownerId = User::where('user_type', 'owner')->first()?->id;
        }

        // 2. Find or create Customer under this Owner
        $customer = Customer::where('email', $fromEmail)
            ->where('owner_id', $ownerId)
            ->first();

        if (!$customer) {
            Log::info("Customer not found for email: {$fromEmail} under owner: {$ownerId}. Auto-creating customer.");
            
            // Extract display name or use prefix of email
            $namePart = explode('@', $fromEmail)[0];
            $customerName = ucfirst(str_replace(['.', '_', '-'], ' ', $namePart));

            $customer = Customer::create([
                'email' => $fromEmail,
                'name' => $customerName,
                'owner_id' => $ownerId,
                'status' => 'New',
            ]);
        }

        // 3. Load Related Orders
        $recentOrders = Order::where('customer_id', $customer->id)
            ->where('owner_id', $ownerId)
            ->orderBy('shopify_created_at', 'desc')
            ->limit(3)
            ->get();

        // 4. Create Ticket with Context
        return $this->createTicket($customer->name, $subject, $body, $ownerId, $recentOrders, $customer->email, $customer->id, $source);
    }

    /**
     * Create a ticket in the system
     */
    private function createTicket($customerName, $subject, $body, $ownerId, $orders = null, $customerEmail = null, $customerId = null, $source = 'Email')
    {
        // If no owner found, we default to the first owner for demo purposes
        if (!$ownerId) {
            $ownerId = User::where('user_type', 'owner')->first()?->id;
        }

        $ticket = Ticket::create([
            'ticket_number' => 'TIC-' . strtoupper(uniqid()),
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'customer_id' => $customerId,
            'subject' => $subject,
            'body' => $body,
            'category' => 'Inquiry',
            'source' => $source,
            'confidence' => $orders && $orders->count() > 0 ? 90 : 40,
            'status' => 'Pending',
            'assigned' => 'AI Agent',
            'owner_id' => $ownerId,
        ]);

        // Here you would also store the email body and order context in a 'messages' or 'ticket_details' table.
        // For now, we'll just log the success.
        
        Log::info("Ticket created: {$ticket->ticket_number} for customer: {$customerName}");

        return response()->json([
            'status' => 'success',
            'ticket_id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'matched_orders_count' => $orders ? $orders->count() : 0
        ], 201);
    }
}
