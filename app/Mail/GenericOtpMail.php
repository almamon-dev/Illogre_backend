<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GenericOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $name;
    public $purpose;

    /**
     * Create a new message instance.
     */
    public function __construct($otp, $name, $purpose = 'Verification')
    {
        $this->otp = $otp;
        $this->name = $name;
        $this->purpose = $purpose;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[{$this->otp}] {$this->purpose}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.generic-otp',
        );
    }
}
