<?php

namespace App\Services\Agent;

use App\Models\AiAutomationSetting;
use App\Models\Ticket;
use Illuminate\Support\Facades\Log;

class TicketAutomationService
{
    /**
     * Process an incoming ticket using AI Automation Rules
     */
    public function processIncomingTicket(Ticket $ticket)
    {
        $suggestedReply = $ticket->ai_suggested_reply;
        $score = $ticket->confidence;

        // 2. Fetch AI Settings for the Owner
       $settings = AiAutomationSetting::where('user_id', $ticket->owner_id)->first();

        if (!$settings) {
            // Default to Human-led if no settings exist
            $this->assignToHuman($ticket);
            return;
        }

        // 3. Decision Engine Based on Mode and Confidence Score
        if ($score <= $settings->human_led_threshold) {
            // Human-Led Zone
            $this->assignToHuman($ticket);
        } elseif ($score > $settings->human_led_threshold && $score <= $settings->ai_assisted_threshold) {
            // AI-Assisted Zone (Co-pilot)
            $this->assignToAgentReview($ticket, $suggestedReply);
        } else {
            // AI-Driven Zone (Autopilot)
            if ($settings->mode === 'autopilot') {
                $this->resolveAutonomously($ticket, $suggestedReply);
            } else {
                // If mode is supervised or copilot, don't let it auto-resolve even if confidence is high
                $this->assignToAgentReview($ticket, $suggestedReply);
            }
        }
    }

    private function assignToHuman(Ticket $ticket)
    {
        $ticket->update([
            'status' => 'Open',
            'assigned' => 'Support Team',
        ]);
        Log::info("Ticket {$ticket->id} assigned to Human (Confidence too low).");
    }

    private function assignToAgentReview(Ticket $ticket, $suggestedReply)
    {
        $ticket->update([
            'status' => 'Pending Review',
            'assigned' => 'Support Agent',
        ]);
        Log::info("Ticket {$ticket->id} assigned for Agent Review (AI Assisted). Draft reply generated.");
    }

    private function resolveAutonomously(Ticket $ticket, $suggestedReply)
    {
        $ticket->update([
            'status' => 'Resolved',
            'assigned' => 'AI Agent',
        ]);
        
        if ($suggestedReply) {
            \App\Models\TicketMessage::create([
                'ticket_id' => $ticket->id,
                'sender_name' => 'AI Agent',
                'body' => $suggestedReply,
                'is_ai' => true,
                'is_internal' => false,
            ]);
        }

        // Call Email service to actually SEND the $suggestedReply to the customer
        if ($ticket->customer_email && $suggestedReply) {
            \Illuminate\Support\Facades\Mail::to($ticket->customer_email)->send(new \App\Mail\TicketReplyMail($suggestedReply, $ticket));
        }
        
        Log::info("Ticket {$ticket->id} resolved autonomously by AI. Reply sent to {$ticket->customer_email}.");
    }
}
