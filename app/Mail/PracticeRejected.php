<?php

namespace App\Mail;

use App\Models\Practice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PracticeRejected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Practice $practice,
        public readonly ?string $rejectionReason,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '❌ Tu práctica requiere correcciones — ' . ($this->practice->empresa?->name_empresa ?? ''),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.practice.rejected',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
