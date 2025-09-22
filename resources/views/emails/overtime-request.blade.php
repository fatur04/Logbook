<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Pengajuan Lembur Baru</title>
</head>
<body>
    <h2>Pengajuan Lembur Baru</h2>

    <p>Halo {{ $recipientName }},</p>

    @switch($type)
        @case('pending')
            <p>Ada pengajuan lembur baru dari <strong>{{ $overtime->nama }} ({{ $overtime->initial }})</strong>.</p>
            @break
        @case('approve')
            <p>Pengajuan lembur dari <strong>{{ $overtime->nama }} ({{ $overtime->initial }})</strong> telah <strong>disetujui</strong>.</p>
            @break
        @case('reject')
            <p>Pengajuan lembur dari <strong>{{ $overtime->nama }} ({{ $overtime->initial }})</strong> telah <strong>ditolak</strong>.</p>
            @break
    @endswitch

    <p><strong>Detail:</strong></p>
    <ul>
        <li>Cluster: {{ $overtime->cluster }}</li>
        <li>Role: {{ $overtime->role }}</li>
        <li>Task: {{ $overtime->activity?->activity ?? 'N/A' }}</li>
        <li>Tanggal: {{ $overtime->start_date }} - {{ $overtime->end_date }}</li>
        {{-- <li>Total Jam: {{ $overtime->total_jam }}</li> --}}
        <li>Total Lembur: {{ $overtime->total_lembur }}</li>
        <li>Status: {{ ucfirst($overtime->status) }}</li>
        <li>Approved By: {{ $overtime->approvedByUser?->name ?? '-' }}</li>
    </ul>

    {{-- Tombol hanya muncul jika status belum final --}}
    @if(in_array($overtime->status, ['pending', 'Engineer_approved']))
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin:15px 0;">
            <tr>
                <td align="left">
                    <a href="{{ $approvalUrl }}" 
                    style="background:#007bff;color:white;padding:8px 16px;text-decoration:none;border-radius:6px;display:inline-block;">
                        Lihat Detail & Approve
                    </a>
                </td>
            </tr>
        </table>
    @endif

    <p>Terima kasih,<br>SEOA</p>
</body>
</html>
