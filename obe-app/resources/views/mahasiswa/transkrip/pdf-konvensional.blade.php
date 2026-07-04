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
                <img src="/home/anugra43/public_html/images/logo_transkrip.png"
                     style="width:60px; height:60px;">
            </td>
            <td style="border:none; vertical-align:middle;">
                <div style="font-size:15pt; font-weight:700; line-height:1.2;">Universitas Riau</div>
                <div style="font-size:10pt; font-weight:700; letter-spacing:.04em;">FAKULTAS TEKNIK</div>
                <div style="font-size:8.5pt; color:#444; margin-top:2px;">Kampus Bina Widya KM 12,5 Simpang Baru, Pekanbaru 28293</div>
            </td>
        </tr>
    </table>
    <hr style="border:none; border-top:3px solid #000; margin-bottom:14px;">

    {{-- JUDUL --}}
    <div style="text-align:center; margin-bottom:14px;">
        <div style="font-size:13pt; font-weight:700; letter-spacing:.06em; text-transform:uppercase;">
            Daftar Prestasi Akademik Mahasiswa Sementara
        </div>
    </div>

    {{-- INFO MAHASISWA --}}
    @php
        $profileMhs     = $user->profilMahasiswa()->with('programStudi')->first();
        $namaProdi      = $profileMhs?->programStudi?->nama_prodi ?? 'Teknik Informatika';
        $jenjangLabel   = match(true) {
            str_starts_with(strtolower($namaProdi), 'd3') || str_contains(strtolower($namaProdi), 'diploma') => 'D3 / Diploma',
            str_starts_with(strtolower($namaProdi), 's2') || str_contains(strtolower($namaProdi), 'magister') => 'S2 / Magister',
            default => 'S1 / Sarjana',
        };
        $totalMutuXsks = 0;
        $totalKredit   = 0;
        foreach ($transcriptRows as $row) {
            $sks = (int)($row['course']->sks ?? 0);
            $totalKredit   += $sks;
            $totalMutuXsks += $row['final_mutu'] * $sks;
        }
        $ipkCetak = $totalKredit > 0 ? round($totalMutuXsks / $totalKredit, 2) : 0;
        $predikat = match(true) {
            $ipkCetak >= 3.51 => 'Dengan Pujian (Cumlaude)',
            $ipkCetak >= 3.01 => 'Sangat Memuaskan',
            $ipkCetak >= 2.76 => 'Memuaskan',
            $ipkCetak >= 2.00 => 'Cukup',
            default           => '—',
        };
    @endphp

    <table style="width:100%; border:none; border-collapse:collapse; margin-bottom:14px; font-size:10.5pt;">
        <tr>
            <td style="border:none; width:180px; padding:2px 4px;">Nama</td>
            <td style="border:none; width:12px; padding:2px 4px;">:</td>
            <td style="border:none; padding:2px 4px;"><strong>{{ $user->name }}</strong></td>
        </tr>
        <tr>
            <td style="border:none; padding:2px 4px;">NIM</td>
            <td style="border:none; padding:2px 4px;">:</td>
            <td style="border:none; padding:2px 4px;">{{ $user->identity }}</td>
        </tr>
        <tr>
            <td style="border:none; padding:2px 4px;">Program Pendidikan Tinggi</td>
            <td style="border:none; padding:2px 4px;">:</td>
            <td style="border:none; padding:2px 4px;">{{ $jenjangLabel }}</td>
        </tr>
        <tr>
            <td style="border:none; padding:2px 4px;">Program Studi</td>
            <td style="border:none; padding:2px 4px;">:</td>
            <td style="border:none; padding:2px 4px;">{{ $namaProdi }}</td>
        </tr>
    </table>

    {{-- TABEL NILAI --}}
    <table style="width:100%; border-collapse:collapse; font-size:10pt; margin-bottom:14px;">
        <thead>
            <tr style="background:#f0f0f0;">
                <th style="text-align:center; width:32px;">No</th>
                <th style="text-align:center; width:100px;">Kode MK</th>
                <th style="text-align:left;">Nama Mata Kuliah</th>
                <th style="text-align:center; width:60px;">SKS</th>
                <th style="text-align:center; width:60px;">Nilai</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transcriptRows as $i => $row)
            <tr>
                <td style="text-align:center;">{{ $i + 1 }}</td>
                <td style="text-align:center;">{{ $row['course']->code }}</td>
                <td>{{ $row['course']->name }}</td>
                <td style="text-align:center; font-weight:600;">{{ $row['course']->sks ?? '-' }}</td>
                <td style="text-align:center; font-weight:700;">{{ $row['final_grade'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- KETERANGAN GRADE + RINGKASAN --}}
    <table style="width:100%; border:none; border-collapse:collapse; margin-bottom:28px;">
        <tr style="vertical-align:top;">
            <td style="border:none; width:52%; padding-right:12px; vertical-align:top;">
                <table style="border-collapse:collapse; width:100%; font-size:9pt;">
                    <thead>
                        <tr style="background:#f0f0f0;">
                            <th style="text-align:center;">Nilai Huruf</th>
                            <th style="text-align:center;">Bobot</th>
                            <th style="text-align:center;">Rentang Skor</th>
                            <th style="text-align:center;">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach([
                            ['A',  '4.00', '≥ 85',    'Sangat Baik'],
                            ['A-', '3.75', '80 – 84', 'Sangat Baik'],
                            ['B+', '3.50', '75 – 79', 'Baik'],
                            ['B',  '3.00', '70 – 74', 'Baik'],
                            ['B-', '2.75', '65 – 69', 'Cukup Baik'],
                            ['C+', '2.50', '60 – 64', 'Cukup'],
                            ['C',  '2.00', '55 – 59', 'Cukup'],
                            ['D',  '1.00', '45 – 54', 'Kurang'],
                            ['E',  '0.00', '< 45',    'Tidak Lulus'],
                        ] as $g)
                        <tr>
                            <td style="text-align:center; font-weight:700;">{{ $g[0] }}</td>
                            <td style="text-align:center;">{{ $g[1] }}</td>
                            <td style="text-align:center;">{{ $g[2] }}</td>
                            <td>{{ $g[3] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
            <td style="border:none; vertical-align:top;">
                <table style="border-collapse:collapse; width:100%; font-size:9.5pt;">
                    <tr>
                        <td colspan="2" style="background:#f0f0f0; font-weight:700; padding:5px 9px;">
                            Ringkasan Prestasi
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:5px 9px;">Nilai Mutu Kualitatif</td>
                        <td style="text-align:center; font-weight:700; padding:5px 9px;">
                            {{ number_format($totalMutuXsks, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:5px 9px;">Kredit Kumulatif (SKS)</td>
                        <td style="text-align:center; font-weight:700; padding:5px 9px;">
                            {{ $totalKredit }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:5px 9px;">Indeks Prestasi Kumulatif</td>
                        <td style="text-align:center; font-weight:700; padding:5px 9px;">
                            {{ number_format($ipkCetak, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:5px 9px;">Predikat Lulus</td>
                        <td style="text-align:center; font-weight:700; padding:5px 9px;">
                            {{ $predikat }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
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