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
        <li>Total Jam: {{ $overtime->total_jam }}</li>
        <li>Total Lembur: {{ $overtime->total_lembur }}</li>
        <li>Status: {{ ucfirst($overtime->status) }}</li>
    </ul>

    {{-- Tombol hanya muncul jika status belum final --}}
    @if(in_array($overtime->status, ['pending', 'Engineer_approved']))
    <p>
        <a href="{{ $approveUrl }}" 
        style="background:green;color:white;padding:10px 15px;text-decoration:none;">
        Approve
        </a>

        <a href="{{ $rejectUrl }}" 
        style="background:red;color:white;padding:10px 15px;text-decoration:none;">
        Reject
        </a>
    </p>

    <p>Terima kasih,<br>SEOA</p>
</body>
</html>
