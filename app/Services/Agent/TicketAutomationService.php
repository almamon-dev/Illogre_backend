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
        // 1. Simulate AI Analysis (In real world, call OpenAI/Anthropic API here)
        $aiAnalysis = $this->simulateAiAnalysis($ticket->body);

        // Update ticket with AI findings
        $ticket->update([
            'confidence' => $aiAnalysis['confidence_score'],
            'category'   => $aiAnalysis['category'],
            'priority'   => $aiAnalysis['risk_level'],
            // 'summary' => $aiAnalysis['summary'], // Add summary column to DB if needed
        ]);

        $suggestedReply = $aiAnalysis['suggested_reply'];

        // 2. Fetch AI Settings for the Owner
        $settings = AiAutomationSetting::where('user_id', $ticket->owner_id)->first();

        if (!$settings) {
            // Default to Human-led if no settings exist
            $this->assignToHuman($ticket);
            return;
        }

        // 3. Decision Engine Based on Mode and Confidence Score
        $score = $aiAnalysis['confidence_score'];

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
            // Here you might store the $suggestedReply in a separate table like `ticket_drafts` or a field
        ]);
        Log::info("Ticket {$ticket->id} assigned for Agent Review (AI Assisted). Draft reply generated.");
    }

    private function resolveAutonomously(Ticket $ticket, $suggestedReply)
    {
        $ticket->update([
            'status' => 'Resolved',
            'assigned' => 'AI Agent',
        ]);
        
        // Call Email/WhatsApp service to actually SEND the $suggestedReply to the customer
        
        Log::info("Ticket {$ticket->id} resolved autonomously by AI. Reply sent.");
    }

    /**
     * Mock AI Response. Replace with actual LLM call.
     */
    private function simulateAiAnalysis($text)
    {
        // Simple mock logic for demonstration
        return [
            'confidence_score' => rand(40, 95),
            'category' => 'General Inquiry',
            'risk_level' => 'Medium',
            'summary' => 'Customer is asking a general question.',
            'suggested_reply' => 'Hello! Thank you for reaching out. Based on your message, here is the information you need...',
        ];
    }
}
