<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AgentInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $managerName;
    public $acceptUrl;
    public $temporaryPassword;
    public $email;

    public function __construct($managerName, $acceptUrl, $temporaryPassword, $email)
    {
        $this->managerName = $managerName;
        $this->acceptUrl = $acceptUrl;
        $this->temporaryPassword = $temporaryPassword;
        $this->email = $email;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invitation to join as Support Agent',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.agent-invitation',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
