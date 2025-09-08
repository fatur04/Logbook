@component('mail::message')
# Pengajuan Lembur Baru

Halo {{ $recipientName }},

@switch($type)
    @case('pending')
        Ada pengajuan lembur baru dari **{{ $overtime->nama }} ({{ $overtime->initial }})**.
        @break

    @case('approve')
        Pengajuan lembur dari **{{ $overtime->nama }} ({{ $overtime->initial }})** telah **disetujui**.
        @break

    @case('reject')
        Pengajuan lembur dari **{{ $overtime->nama }} ({{ $overtime->initial }})** telah **ditolak**.
        @break
@endswitch

**Detail:**
- Cluster: {{ $overtime->cluster }}
- Role: {{ $overtime->role }}
- Task: {{ $overtime->activity?->activity ?? 'N/A' }}
- Tanggal: {{ $overtime->start_date }} - {{ $overtime->end_date }}
- Total Jam: {{ $overtime->total_jam }}
- Total Lembur: {{ $overtime->total_lembur }}
- Status: {{ ucfirst($overtime->status) }}

<!-- @component('mail::button', ['url' => url('/admin/overtimes')])
Lihat di Sistem
@endcomponent -->

Terima kasih,<br>
SEOA<br>
@endcomponent
