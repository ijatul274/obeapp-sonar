<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KHS - {{ $user->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            background: #f0f2f5;
            color: #000;
        }

        /* ── SCREEN: wrapper & tombol ── */
        .screen-wrapper {
            max-width: 860px;
            margin: 0 auto;
            padding: 24px 16px;
        }
        .screen-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
        }
        .screen-controls h1 {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
        }
        .screen-controls .subtitle {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: .8rem;
            color: #64748b;
            margin-top: 2px;
        }
        .btn-group { display: flex; gap: 8px; flex-wrap: wrap; }
        .btn {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: .8rem;
            font-weight: 600;
            padding: .45rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: .15s;
        }
        .btn-back {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
        .btn-back:hover { background: #e2e8f0; }
        .btn-print {
            background: #c0392b;
            color: #fff;
        }
        .btn-print:hover { background: #a93226; }

        /* semester selector */
        .sem-selector {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }
        .sem-selector a {
            font-size: .78rem;
            font-weight: 600;
            padding: .35rem .85rem;
            border-radius: 20px;
            text-decoration: none;
            border: 1.5px solid #e2e8f0;
            color: #475569;
            background: #fff;
            transition: .15s;
        }
        .sem-selector a.active,
        .sem-selector a:hover {
            background: #c0392b;
            color: #fff;
            border-color: #c0392b;
        }

        /* ── KHS DOCUMENT ── */
        .khs-doc {
            background: #fff;
            border-radius: 6px;
            box-shadow: 0 1px 12px rgba(0,0,0,.10);
            padding: 40px 44px 44px;
        }

        /* Header */
        .khs-header {
            display: flex;
            align-items: center;
            gap: 16px;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 18px;
        }
        .khs-header__logo {
            width: 64px;
            height: 64px;
            flex-shrink: 0;
        }
        .khs-header__text {}
        .khs-header__univ {
            font-size: 15pt;
            font-weight: 700;
            line-height: 1.2;
        }
        .khs-header__fakultas {
            font-size: 10pt;
            font-weight: 700;
            letter-spacing: .04em;
        }
        .khs-header__address {
            font-size: 8.5pt;
            color: #555;
            margin-top: 2px;
        }

        /* Judul */
        .khs-title {
            text-align: center;
            margin: 10px 0 16px;
        }
        .khs-title h2 {
            font-size: 14pt;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .khs-title .semester-label {
            font-size: 10pt;
            font-weight: 600;
            margin-top: 2px;
        }

        /* Info mahasiswa */
        .khs-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 10.5pt;
        }
        .khs-info td { padding: 2px 4px; vertical-align: top; }
        .khs-info td:first-child { width: 150px; }
        .khs-info td:nth-child(2) { width: 14px; }

        /* Tabel nilai */
        .khs-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            margin-bottom: 16px;
        }
        .khs-table th, .khs-table td {
            border: 1px solid #000;
            padding: 5px 7px;
            vertical-align: middle;
        }
        .khs-table thead th {
            background: #f5f5f5;
            font-weight: 700;
            text-align: center;
            font-size: 9.5pt;
        }
        .khs-table tbody td { font-size: 10pt; }
        .khs-table tfoot td {
            font-weight: 700;
            font-size: 10pt;
        }
        .text-center { text-align: center; }
        .text-right  { text-align: right; }

        /* IPS/IPK summary */
        .khs-summary {
            font-size: 10.5pt;
            margin-bottom: 28px;
        }
        .khs-summary table { border-collapse: collapse; }
        .khs-summary td { padding: 2px 4px; }
        .khs-summary td:first-child { width: 180px; }
        .khs-summary td:nth-child(2) { width: 14px; }

        /* Tanda tangan */
        .khs-sign {
            display: flex;
            justify-content: flex-end;
            margin-top: 8px;
        }
        .khs-sign__box {
            text-align: center;
            font-size: 10.5pt;
            min-width: 220px;
        }
        .khs-sign__space { height: 72px; }
        .khs-sign__name {
            font-weight: 700;
            border-top: 1px solid #000;
            padding-top: 4px;
            display: inline-block;
            min-width: 200px;
        }
        .khs-sign__nip { font-size: 9.5pt; margin-top: 2px; }

        /* ── PRINT ── */
        @media print {
            @page {
                size: A4;
                margin: 1.5cm 1.5cm 2cm;
            }
            body { background: #fff !important; }
            .screen-wrapper { padding: 0; max-width: 100%; }
            .screen-controls,
            .sem-selector { display: none !important; }
            .khs-doc {
                box-shadow: none !important;
                border-radius: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body>

<div class="screen-wrapper">

    {{-- ── TOMBOL NAVIGASI (disembunyikan saat cetak) ── --}}
    <div class="screen-controls">
        <div>
            <h1>Kartu Hasil Studi (KHS)</h1>
            <div class="subtitle">{{ $user->name }} &mdash; {{ $user->identity }}</div>
        </div>
        <div class="btn-group">
            <a href="{{ route('mahasiswa.transkrip') }}" class="btn btn-back">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Kembali
            </a>
            <button onclick="window.print()" class="btn btn-print">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak / Download KHS
            </button>
        </div>
    </div>

    {{-- ── PILIH SEMESTER (disembunyikan saat cetak) ── --}}
    @if(count($semesterList) > 0)
    <div class="sem-selector">
        @foreach($semesterList as $sem)
            <a href="{{ route('mahasiswa.khs', ['period_type' => $sem['period_type'], 'academic_year' => $sem['academic_year']]) }}"
               class="{{ $sem['period_type'] === $activePeriodType && $sem['academic_year'] === $activeAcademicYear ? 'active' : '' }}">
                {{ ucfirst($sem['period_type']) }} {{ $sem['academic_year'] }}
            </a>
        @endforeach
    </div>
    @endif

    {{-- ── DOKUMEN KHS ── --}}
    <div class="khs-doc">

        {{-- Header kop surat --}}
        <div class="khs-header">
            {{-- Logo Universitas Riau — Base64 kecil sebagai fallback jika logo ada di storage --}}
            @if(file_exists(public_path('images/logo-unri.png')))
                <img src="{{ asset('images/logo-unri.png') }}" alt="Logo UNRI" class="khs-header__logo">
            @else
                {{-- SVG placeholder berbentuk lingkaran dengan inisial jika logo tidak ada --}}
                <svg class="khs-header__logo" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="32" cy="32" r="30" fill="none" stroke="#003366" stroke-width="3"/>
                    <circle cx="32" cy="32" r="22" fill="none" stroke="#003366" stroke-width="1.5"/>
                    <text x="32" y="26" text-anchor="middle" font-family="serif" font-size="9" font-weight="bold" fill="#003366">UNIVERSITAS</text>
                    <text x="32" y="37" text-anchor="middle" font-family="serif" font-size="11" font-weight="bold" fill="#003366">RIAU</text>
                    <text x="32" y="47" text-anchor="middle" font-family="serif" font-size="7" fill="#003366">UNRI</text>
                </svg>
            @endif
            <div class="khs-header__text">
                <div class="khs-header__univ">Universitas Riau</div>
                <div class="khs-header__fakultas">FAKULTAS TEKNIK</div>
                <div class="khs-header__address">
                    Kampus Bina Widya KM 12,5 Simpang Baru, Pekanbaru 28293
                </div>
            </div>
        </div>

        {{-- Judul --}}
        <div class="khs-title">
            <h2>Kartu Hasil Studi</h2>
            <div class="semester-label">
                Semester : {{ ucfirst($activePeriodType) }} {{ $activeAcademicYear }}
            </div>
        </div>

        {{-- Info Mahasiswa --}}
        <table class="khs-info">
            <tr>
                <td>Nama Mahasiswa</td><td>:</td>
                <td><strong>{{ $user->name }}</strong></td>
            </tr>
            <tr>
                <td>Nomor Induk Mahasiswa</td><td>:</td>
                <td>{{ $user->identity }}</td>
            </tr>
            <tr>
                <td>Angkatan</td><td>:</td>
                <td>{{ $angkatan }}</td>
            </tr>
            <tr>
                <td>Program Studi</td><td>:</td>
                <td>{{ $namaProdi }}</td>
            </tr>
            <tr>
                <td>Pembimbing Akademik</td><td>:</td>
                <td>{{ $pembimbingAkademik ?? '—' }}</td>
            </tr>
        </table>

        {{-- Tabel Nilai --}}
        <table class="khs-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width:34px;">No</th>
                    <th rowspan="2" colspan="2">Matakuliah</th>
                    <th rowspan="2" style="width:40px;">SKS</th>
                    <th rowspan="2" style="width:30px;">KE</th>
                    <th rowspan="2" style="width:44px;">Nilai</th>
                    <th rowspan="2" style="width:54px;">BOBOT</th>
                    <th style="width:60px;">NILAI SKS</th>
                </tr>
                <tr>
                    <th style="width:60px;">&nbsp;</th>
                </tr>
                <tr>
                    <th></th>
                    <th style="width:90px;">KODE</th>
                    <th>NAMA</th>
                    <th></th><th></th><th></th><th></th><th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($khsRows as $i => $row)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $row['course']->code }}</td>
                        <td>{{ $row['course']->name }}</td>
                        <td class="text-center">{{ $row['course']->sks ?? '-' }}</td>
                        <td class="text-center">{{ $row['ke'] ?? 1 }}</td>
                        <td class="text-center">
                            @if($row['final_grade'] && $row['final_grade'] !== '-')
                                {{ $row['final_grade'] }}
                            @endif
                        </td>
                        <td class="text-center">
                            @if($row['final_grade'] && $row['final_grade'] !== '-')
                                {{ number_format($row['final_mutu'], 2) }}
                            @endif
                        </td>
                        <td class="text-center">
                            @if($row['final_grade'] && $row['final_grade'] !== '-')
                                {{ $row['nilai_sks'] !== null ? number_format($row['nilai_sks'], 2) : '' }}
                            @else
                                0.00
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding:16px; font-style:italic; color:#888;">
                            Tidak ada data untuk semester ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right">Jumlah</td>
                    <td class="text-center">{{ $totalSks }}</td>
                    <td colspan="3"></td>
                    <td class="text-center">{{ number_format($totalNilaiSks, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- IPS / IPK --}}
        <div class="khs-summary">
            <table>
                <tr>
                    <td>IP Semester (IPS)</td>
                    <td>:</td>
                    <td><strong>{{ $ips > 0 ? number_format($ips, 2) : '0.00' }}</strong></td>
                </tr>
                <tr>
                    <td>IP Kumulatif (IPK)</td>
                    <td>:</td>
                    <td><strong>{{ $ipk > 0 ? number_format($ipk, 2) : '' }}</strong></td>
                </tr>
                <tr>
                    <td>Maks. Beban sks semester berikutnya</td>
                    <td>:</td>
                    <td><strong>{{ $maxSksBerikutnya > 0 ? $maxSksBerikutnya : '' }}</strong></td>
                </tr>
            </table>
        </div>

        {{-- Tanda Tangan --}}
        <div class="khs-sign">
            <div class="khs-sign__box">
                <div>Pekanbaru, {{ now()->translatedFormat('d F Y') }}</div>
                <div style="margin-top:4px;">Mengesahkan</div>
                <div>Wakil Dekan Bidang Akademis</div>
                <div class="khs-sign__space"></div>
                <div>
                    <span class="khs-sign__name">{{ $wakilDekan ?? 'Prof. Dr. Ir. Azriyenni, ST., M.Eng' }}</span>
                </div>
                <div class="khs-sign__nip">NIP. {{ $nipWakilDekan ?? '197304011999032003' }}</div>
            </div>
        </div>

    </div>{{-- .khs-doc --}}

</div>{{-- .screen-wrapper --}}

</body>
</html>