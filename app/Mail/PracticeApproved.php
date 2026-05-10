<?php

namespace App\Mail;

use App\Models\Practice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PracticeApproved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Practice $practice,
        public readonly string $pdfUrl,
        public readonly string $pdfPath,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✅ Tu práctica fue aprobada — ' . $this->practice->name_empresa,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.practice.approved',
        );
    }

    public function attachments(): array
    {
        if (!file_exists($this->pdfPath)) {
            return [];
        }

        return [
            Attachment::fromPath($this->pdfPath)
                ->as('Carta_Presentacion_' . $this->practice->name_empresa . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
