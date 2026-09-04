<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @param array{name: string, email: string, subject?: string|null, message: string} $contact */
    public function __construct(public array $contact) {}

    public function envelope(): Envelope
    {
        $subject = trim((string) ($this->contact['subject'] ?? ''));

        return new Envelope(
            subject: 'Website contact: '.($subject ?: 'New message'),
            replyTo: [new Address($this->contact['email'], $this->contact['name'])],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact-message');
    }
}
