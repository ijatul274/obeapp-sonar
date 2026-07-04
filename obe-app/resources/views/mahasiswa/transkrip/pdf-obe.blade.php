<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Times New Roman', serif; font-size: 10pt; margin: 0; padding: 0; }
        @page { margin: 1.5cm 1.5cm 2cm; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px 6px; }
    </style>
</head>
<body>

    {{-- KOP SURAT --}}
    <table style="width:100%; border:none; margin-bottom:10px;">
        <tr>
            <td style="border:none; width:70px; vertical-align:middle;">
                <img src="{{ public_path('images/logo_transkrip.png') }}"
                     style="width:60px; height:60px;">
            </td>
            <td style="border:none; vertical-align:middle;">
                <div style="font-size:15pt; font-weight:700; line-height:1.2;">Universitas Riau</div>
                <div style="font-size:10pt; font-weight:700;">FAKULTAS TEKNIK</div>
                <div style="font-size:8.5pt; color:#444; margin-top:2px;">Kampus Bina Widya KM 12,5 Simpang Baru, Pekanbaru 28293</div>
            </td>
        </tr>
    </table>
    <hr style="border:none; border-top:3px solid #000; margin-bottom:14px;">

    {{-- JUDUL --}}
    <div style="text-align:center; margin-bottom:14px;">
        <div style="font-size:13pt; font-weight:700; text-transform:uppercase;">
            Transkrip Nilai OBE (Capaian Pembelajaran Lulusan)
        </div>
    </div>

    {{-- INFO MAHASISWA --}}
    @php
        $profileMhs2     = $user->profilMahasiswa()->with('programStudi')->first();
        $namaProdiCetak2 = $profileMhs2?->programStudi?->nama_prodi ?? 'Teknik Informatika';
    @endphp
    <table style="width:100%; border:none; border-collapse:collapse; margin-bottom:14px; font-size:10.5pt;">
        <tr>
            <td style="border:none; width:170px; padding:2px 4px;">Nama Mahasiswa</td>
            <td style="border:none; width:12px; padding:2px 4px;">:</td>
            <td style="border:none; padding:2px 4px;"><strong>{{ $user->name }}</strong></td>
            <td style="border:none; width:100px; padding:2px 4px;">Total SKS</td>
            <td style="border:none; width:12px; padding:2px 4px;">:</td>
            <td style="border:none; padding:2px 4px;"><strong>{{ $totalSks }}</strong></td>
        </tr>
        <tr>
            <td style="border:none; padding:2px 4px;">Nomor Induk Mahasiswa</td>
            <td style="border:none; padding:2px 4px;">:</td>
            <td style="border:none; padding:2px 4px;">{{ $user->identity }}</td>
            <td style="border:none; padding:2px 4px;">IPK</td>
            <td style="border:none; padding:2px 4px;">:</td>
            <td style="border:none; padding:2px 4px;"><strong>{{ number_format($ipk, 2) }}</strong></td>
        </tr>
        <tr>
            <td style="border:none; padding:2px 4px;">Program Studi</td>
            <td style="border:none; padding:2px 4px;">:</td>
            <td style="border:none; padding:2px 4px;">{{ $namaProdiCetak2 }}</td>
            <td style="border:none; padding:2px 4px;">Tanggal Cetak</td>
            <td style="border:none; padding:2px 4px;">:</td>
            <td style="border:none; padding:2px 4px;">{{ now()->translatedFormat('d F Y') }}</td>
        </tr>
    </table>

    {{-- TABEL NILAI MK --}}
    <table style="width:100%; border-collapse:collapse; font-size:9.5pt; margin-bottom:14px;">
        <thead>
            <tr style="background:#f0f0f0;">
                <th style="text-align:center; width:28px;">No</th>
                <th style="text-align:center; width:90px;">Kode MK</th>
                <th style="text-align:left;">Nama Mata Kuliah</th>
                <th style="text-align:center; width:34px;">SKS</th>
                <th style="text-align:center; width:100px;">Semester</th>
                <th style="text-align:center; width:46px;">Nilai</th>
                <th style="text-align:center; width:50px;">Bobot</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transcriptRows as $i => $row)
            @php
                $ay    = $row['classroom']->academic_year ?? '';
                $tahun = $ay ? explode('/', $ay)[0] : '';
            @endphp
            <tr>
                <td style="text-align:center;">{{ $i + 1 }}</td>
                <td style="text-align:center; font-size:8.5pt;">{{ $row['course']->code }}</td>
                <td>{{ $row['course']->name }}</td>
                <td style="text-align:center; font-weight:600;">{{ $row['course']->sks ?? '-' }}</td>
                <td style="font-size:9pt;">
                    {{ ucfirst($row['classroom']->period_type ?? '') }}
                    @if($tahun) {{ $tahun }} @endif
                </td>
                <td style="text-align:center; font-weight:700;">{{ $row['final_grade'] }}</td>
                <td style="text-align:center;">{{ number_format($row['final_mutu'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background:#f0f0f0;">
                <td colspan="3" style="text-align:right; font-weight:700;">Total SKS / IPK</td>
                <td style="text-align:center; font-weight:700;">{{ $totalSks }}</td>
                <td colspan="2" style="text-align:right; font-weight:600;">IPK</td>
                <td style="text-align:center; font-weight:700;">{{ number_format($ipk, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- TABEL CPL --}}
    <div style="font-size:10.5pt; font-weight:700; margin-bottom:6px; margin-top:4px;">
        Ketercapaian Capaian Pembelajaran Lulusan (CPL)
    </div>
    <table style="width:100%; border-collapse:collapse; font-size:9.5pt; margin-bottom:20px;">
        <thead>
            <tr style="background:#f0f0f0;">
                <th style="text-align:center; width:90px;">Kode CPL</th>
                <th style="text-align:left;">Pernyataan CPL</th>
                <th style="text-align:center; width:90px;">Target Min.</th>
                <th style="text-align:center; width:110px;">Ketercapaian</th>
                <th style="text-align:center; width:70px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cplAchievement as $row)
            @php
                $avg       = $row['average'];
                $minTarget = $row['min_target'] ?? 70;
                $lulus     = $avg !== null && $avg >= $minTarget;
            @endphp
            <tr>
                <td style="text-align:center; font-weight:700;">{{ $row['cpl']->code }}</td>
                <td style="font-size:9pt;">{{ $row['cpl']->description ?? '—' }}</td>
                <td style="text-align:center;">{{ rtrim(rtrim(number_format($minTarget, 2, '.', ''), '0'), '.') }}%</td>
                <td style="text-align:center; font-weight:700;">
                    @if($avg !== null) {{ number_format($avg, 1) }}%
                    @else <span style="color:#888; font-style:italic;">Belum ada nilai</span>
                    @endif
                </td>
                <td style="text-align:center; font-weight:700;
                           color:{{ $avg === null ? '#888' : ($lulus ? '#166534' : '#b91c1c') }};">
                    @if($avg !== null) {{ $lulus ? 'Tercapai' : 'Belum' }}
                    @else — @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- TANDA TANGAN --}}
    <table style="width:100%; border:none; font-size:10.5pt;">
        <tr>
            <td style="border:none; width:55%;"></td>
            <td style="border:none; text-align:center;">
                <div>Pekanbaru, {{ now()->isoFormat('D MMMM YYYY') }}</div>
                <div style="margin-top:3px;">Wakil Dekan Bidang Akademis</div>
                <div style="height:72px;"></div>
                <div style="display:inline-block; border-top:1px solid #000;
                            padding-top:4px; min-width:200px; font-weight:700;">
                    Prof. Dr. Ir. Azriyenni, ST., M.Eng
                </div>
                <div style="font-size:9.5pt;">NIP. 197304011999032003</div>
            </td>
        </tr>
    </table>

</body>
</html>