{{--
    Partial: TRANSKRIP OBE
    Param:
      $cplRows : array<int, array{cpl: object{code,description}, average: float|null, sample_count?: int}>
      $title   : string (opsional, default "Transkrip OBE")
      $subtitle: string (opsional)
--}}
@php
    $title    = $title    ?? 'Transkrip OBE';
    $subtitle = $subtitle ?? 'Ketercapaian Capaian Pembelajaran Lulusan (CPL)';
@endphp

<div class="obe-card p-0 overflow-hidden mt-4">
    <div class="px-3 py-3 border-bottom" style="background:var(--obe-bg);">
        <h2 class="obe-card__title mb-1">{{ $title }}</h2>
        <small class="text-muted">{{ $subtitle }}</small>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0" style="font-size:.82rem;">
            <thead style="background:var(--obe-ink); color:#fff;">
                <tr>
                    <th class="text-center" style="background:var(--obe-ink); color:#fff; width:50px;">No</th>
                    <th class="text-center" style="background:var(--obe-ink); color:#fff; width:120px;">Kode CPL</th>
                    <th style="background:var(--obe-ink); color:#fff;">Pernyataan CPL</th>
                    <th class="text-center" style="background:var(--obe-ink); color:#fff; width:120px;">CPMK Diambil</th>
                    <th class="text-center" style="background:var(--obe-ink); color:#fff; width:110px;">Min. Target</th>
                    <th class="text-center" style="background:var(--obe-ink); color:#fff; width:140px;">Ketercapaian</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cplRows as $i => $row)
                    @php
                        $avg        = $row['average'];
                        $minTarget  = $row['min_target'] ?? 70;
                        $supportN   = $row['support_count'] ?? null;
                        $takenN     = $row['taken_count']   ?? null;
                        $color      = $avg === null ? '#6b7280' : ($avg >= $minTarget ? '#16a34a' : 'var(--obe-red)');
                    @endphp
                    <tr>
                        <td class="text-center text-muted small">{{ $loop->iteration }}</td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border" style="font-family:monospace;">
                                {{ $row['cpl']->code }}
                            </span>
                        </td>
                        <td>{{ $row['cpl']->description ?? '—' }}</td>
                        <td class="text-center small">
                            @if($supportN !== null)
                                {{ $takenN ?? 0 }} / {{ $supportN }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center small">{{ rtrim(rtrim(number_format($minTarget, 2, '.', ''), '0'), '.') }}%</td>
                        <td class="text-center fw-bold" style="color:{{ $color }};">
                            @if($avg === null)
                                <span class="text-muted">—</span>
                            @else
                                {{ number_format($avg, 1) }}%
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <em>Belum ada data ketercapaian CPL.</em>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-3 py-2 border-top small text-muted" style="background:var(--obe-bg);">
        <em>Catatan:</em> Tiap CPL didistribusikan rata 100% ke seluruh CPMK pendukungnya (CPL dengan N CPMK → tiap CPMK = {{ '(100/N)%' }}). Persentase ketercapaian = jumlah skor CPMK yang diambil ÷ N.
    </div>
</div>
