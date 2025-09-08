<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Overtime;

class OvertimeRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $overtime;
    public $recipientName;
    public $type; // approve/reject

    public function __construct(Overtime $overtime, $recipientName, $type = 'pending')
    {
        $this->overtime = $overtime;
        $this->recipientName = $recipientName;
        $this->type = $type;
    }

    public function build()
    {
        return $this->subject('Pengajuan Lembur Baru - ' . $this->overtime->nama)
            ->markdown('emails.overtime.request');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Overtime Request Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.overtime-request',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
