<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Surat Tugas Lembur</title>
<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 12px;
        margin: 0;
        padding: 20px;
    }
    /* Header logo + judul */
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .logo img {
        max-width: 120px;
        height: auto;
    }
    .title {
        text-align: center;
        flex-grow: 1;
    }
    .title h1 {
        margin: 0;
        font-size: 16px;
        font-weight: bold;
        text-decoration: underline;
    }

    /* Info pegawai & lembur */
    .info-section {
        margin-top: 10px;
    }
    .info-block p {
        margin: 0;
        line-height: 1.5;
    }
    .label {
        display: inline-block;
        width: 120px;
        font-weight: bold;
    }

    /* Task list */
    .task-section {
        margin-top: 15px;
    }
    .task-list {
        list-style-type: disc;
        padding-left: 20px;
        margin: 5px 0;
    }

    /* Tanggal sebelum tanda tangan */
    .date-location {
        text-align: right;
        margin: 20px 0 10px 0;
        font-weight: bold;
    }

    /* Grid + float untuk tanda tangan */
    .signature-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        column-gap: 20px;
        margin-top: 10px;
    }
    .signature-block {
        position: relative;
        text-align: center;
    }
    .signature-left {
        float: left;
    }
    .signature-right {
        float: right;
    }
    .signature-line {
        border-bottom: 1px solid #000;
        height: 60px;
        margin-bottom: 3px;
        display: flex;
        justify-content: center;
        align-items: flex-end;
    }
    .signature-image {
        max-height: 55px;
        max-width: 120px;
    }
    .signature-name {
        font-weight: bold;
        margin-top: 2px;
    }
    .signature-nik {
        margin-top: 2px;
    }
</style>
</head>
<body>

<div class="header">
    <div class="logo">
        <p style="font-size: 11px; margin:0; text-align:left;">Form Lembur Karyawan Shift</p>
        @php
            $logo = public_path('images/seoa.png'); // fallback

            if ($overtime->user?->perusahaan === 'LMD') {
                $logo = public_path('images/lmd.png');
            } elseif ($overtime->user?->perusahaan === 'KSPS') {
                $logo = public_path('images/ksps.png');
            } elseif ($overtime->user?->perusahaan === 'GSS') {
                $logo = public_path('images/gss.png');
            }
        @endphp   

        <img src="file://{{ $logo }}" alt="Logo Perusahaan" style="width:120px;">
    </div>

    <div class="title">
        <h1>SURAT TUGAS LEMBUR</h1>
    </div>

    <div class="logo" style="visibility:hidden;">
        <img src="" alt="" style="width:120px;">
    </div>
</div>

<div class="info-section">
    <p>Diinstruksikan kepada :</p>
    <div class="info-block">
        <p><span class="label">Nama</span>: {{ $overtime->nama }}</p>
        <p><span class="label">NIK</span>: {{ $overtime->nik ?? '-' }}</p>
        <p><span class="label">Bagian/Divisi</span>: PMD</p>
        <p><span class="label">Lokasi Kerja</span>: {{ $overtime->cluster ?? '-' }}</p>
    </div>
</div>

<div class="info-section">
    <p>Untuk melaksanakan lembur pada :</p>
    <div class="info-block">
        <p><span class="label">Hari / Tanggal</span>: {{ \Carbon\Carbon::parse($overtime->start_date)->translatedFormat('l, d F Y') }}</p>
        <p><span class="label">Jam</span>: {{ \Carbon\Carbon::parse($overtime->start_date)->format('H:i') }} - {{ \Carbon\Carbon::parse($overtime->end_date)->format('H:i') }}</p>
    </div>
</div>

<div class="task-section">
    <p>Pelaksanaan lembur diperlukan untuk menyelesaikan tugas sebagai berikut :</p>
    <ul class="task-list">
        <li>{{ $overtime->activity?->activity ?? '-' }}</li>
    </ul>
</div>

<div class="date-location">
    Jakarta, {{ \Carbon\Carbon::parse($overtime->start_date)->translatedFormat('d-m-Y') }}
</div>

<div class="signature-grid">
    <!-- User kiri -->
    <div class="signature-block signature-left">
        <p>Mengetahui atasan langsung</p>
        <div class="signature-line">
            @if($overtime->approvedByUser?->signature_path)
                <img class="signature-image" src="{{ public_path('storage/' . $overtime->approvedByUser->signature_path) }}" alt="Signature">
            @endif
        </div>
        <div class="signature-name"> {{ $overtime->approvedByUser->name ?? 'Atasan' }} </div>
        <div class="signature-nik">NIK: {{ $overtime->approvedByUser->nik ?? '-' }}</div>
    </div>

    <!-- Approval kanan -->
    <div class="signature-block signature-right">
        <p>Yang diberi tugas</p>
        <div class="signature-line">
            @if($overtime->user?->signature_path)
                <img class="signature-image" src="{{ public_path('storage/' . $overtime->user->signature_path) }}" alt="Signature">
            @endif
        </div>
        <div class="signature-name"> {{ $overtime->user->name ?? '-' }} </div>
        <div class="signature-nik">NIK: {{ $overtime->user->nik ?? '-' }}</div> <!-- ambil dari user table -->
    </div>
</div>

</body>
</html>
