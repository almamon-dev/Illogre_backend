<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
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

        if (!$fromEmail) {
            return response()->json(['error' => 'Sender email missing'], 400);
        }

        Log::info("Inbound Email Received: From {$fromEmail} to {$toEmail}");

        // 1. Identify the Owner (Business)
        // In a real app, you'd match $toEmail to a specific workspace/owner.
        // For this demo, we'll try to find any owner who has a customer with this email.
        $customer = Customer::where('email', $fromEmail)->first();

        if (!$customer) {
            Log::info("Customer not found for email: {$fromEmail}. Creating a new record or handling as guest.");
            // We'll skip order matching if customer doesn't exist
            return $this->createTicket($fromEmail, $subject, $body, null, null, $fromEmail);
        }

        $ownerId = $customer->owner_id;

        // 2. Load Related Orders
        $recentOrders = Order::where('customer_id', $customer->id)
            ->where('owner_id', $ownerId)
            ->orderBy('shopify_created_at', 'desc')
            ->limit(3)
            ->get();

        // 3. Create Ticket with Context
        return $this->createTicket($customer->name, $subject, $body, $ownerId, $recentOrders, $customer->email, $customer->id);
    }

    /**
     * Create a ticket in the system
     */
    private function createTicket($customerName, $subject, $body, $ownerId, $orders = null, $customerEmail = null, $customerId = null)
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
            'category' => 'Inquiry',
            'source' => 'Email',
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
