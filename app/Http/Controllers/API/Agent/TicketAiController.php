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
            'reply_text' => 'required|string'
        ]);

        // Here you would typically integrate with Mail / WhatsApp API to actually send the message.
        // Mail::to($ticket->customer_email)->send(new TicketReplyMail($request->reply_text));

        $ticket->update([
            'status' => 'Resolved',
            'assigned' => $request->user()->name // Agent who approved/sent it
        ]);

        return $this->sendResponse($ticket, 'Reply sent to the customer successfully.');
    }
}
