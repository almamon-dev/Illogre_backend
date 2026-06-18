<?php

namespace App\Http\Controllers\API\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Traits\ApiResponse;

class TicketAiController extends Controller
{
    use ApiResponse;

    /**
     * Generate a new AI reply for the ticket.
     */
    public function generateSuggestedReply(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        // Simulate calling an AI service (OpenAI/Anthropic) to generate a response
        $aiReply = "Hello {$ticket->customer_name}, this is an updated AI-generated response regarding your ticket about '{$ticket->subject}'. Let us know if you have any questions!";

        $ticket->update([
            'ai_suggested_reply' => $aiReply
        ]);

        return $this->sendResponse($ticket, 'AI Suggested Reply generated successfully.');
    }

    /**
     * Send the approved AI reply (or edited reply) to the customer.
     */
    public function sendReply(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $request->validate([
            'reply_text' => 'required|string',
            'is_internal' => 'boolean'
        ]);

        $isInternal = $request->input('is_internal', false);

        // Save the message in the database
        $message = \App\Models\TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_name' => $request->user()->name,
            'body' => $request->reply_text,
            'is_ai' => false,
            'is_internal' => $isInternal,
        ]);

        // Only send email if it's a public reply
        if (!$isInternal && $ticket->customer_email) {
            \Illuminate\Support\Facades\Mail::to($ticket->customer_email)
                ->send(new \App\Mail\TicketReplyMail($request->reply_text, $ticket));
        }

        $ticket->update([
            'status' => $isInternal ? $ticket->status : 'Resolved',
            'assigned' => $request->user()->name // Agent who approved/sent it
        ]);

        return $this->sendResponse($message, 'Reply added successfully.');
    }
}
