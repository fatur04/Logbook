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
        $approveUrl = route('overtimes.approve', ['token' => $this->overtime->approval_token]);
        $rejectUrl  = route('overtimes.reject', ['token' => $this->overtime->approval_token]);

        return $this->subject('Pengajuan Lembur Baru - ' . $this->overtime->nama)
            ->view('emails.overtime-request')
            ->with([
                'overtime'   => $this->overtime,
                'type'       => $this->type,
                'approveUrl' => $approveUrl,
                'rejectUrl'  => $rejectUrl,
                'recipientName' => $this->recipientName,
            ]);
    }
}
