<x-sidebar-layout :title="'Persetujuan CPMK'" :header="'Persetujuan CPMK'">

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
        <p class="text-muted small mb-0">Tinjau dan setujui CPMK yang diajukan dosen pengampu.</p>
        @if($pendingCount > 0)
            <span class="badge px-3 py-2" style="background:var(--obe-red-soft); color:var(--obe-red);">{{ $pendingCount }} menunggu persetujuan</span>
        @endif
    </div>

    <form method="GET" class="obe-card mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-uppercase" style="font-size:.7rem;">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="pending"  {{ request('status') == 'pending'  ? 'selected' : '' }}>Menunggu</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    <option value="draft"    {{ request('status') == 'draft'    ? 'selected' : '' }}>Draft</option>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label small fw-semibold text-uppercase" style="font-size:.7rem;">Kelas</label>
                <select name="classroom_id" class="form-select form-select-sm">
                    <option value="">Semua Kelas</option>
                    @foreach($classrooms as $cls)
                        <option value="{{ $cls->id }}" {{ request('classroom_id') == $cls->id ? 'selected' : '' }}>
                            {{ $cls->name }} — {{ $cls->course?->code }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-obe-red btn-sm">Filter</button>
                <a href="{{ route('kaprodi.cpmk-approvals.index') }}" class="btn btn-obe-outline btn-sm">Reset</a>
            </div>
        </div>
    </form>

    <div class="obe-card p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 obe-dt"
                   data-no-sort="0,7"
                   data-filter-cols="6:Status"
                   data-page-length="50">
                <thead>
                    <tr>
                        <th class="text-center" style="width:40px;">No</th>
                        <th>CPMK</th>
                        <th>MK / Kelas</th>
                        <th>CPL</th>
                        <th>Dosen</th>
                        <th class="text-center" style="width:80px;">Bobot</th>
                        <th class="text-center" style="width:130px;">Status</th>
                        <th class="text-center" style="width:90px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cpmks as $i => $cpmk)
                        @php
                            $statusMap = [
                                'draft'    => ['label'=>'Draft',    'bg'=>'#e5e7eb', 'fg'=>'#374151'],
                                'pending'  => ['label'=>'Menunggu', 'bg'=>'#fef3c7', 'fg'=>'#92400e'],
                                'approved' => ['label'=>'Disetujui','bg'=>'#d1fae5', 'fg'=>'#065f46'],
                                'rejected' => ['label'=>'Ditolak',  'bg'=>'#fee2e2', 'fg'=>'#991b1b'],
                            ];
                            $sc = $statusMap[$cpmk->status] ?? $statusMap['draft'];
                        @endphp
                        <tr>
                            <td class="text-center text-muted small">{{ $i + 1 }}</td>
                            <td>
                                <div class="fw-bold" style="font-family:monospace;">{{ $cpmk->code }}</div>
                                <div class="text-muted small text-truncate" style="max-width:280px;">{{ Str::limit($cpmk->description, 60) }}</div>
                            </td>
                            <td class="small">
                                <div class="fw-semibold">{{ $cpmk->classroom?->course?->code }}</div>
                                <div class="text-muted">{{ $cpmk->classroom?->name }}</div>
                                <div class="text-muted" style="font-size:.7rem;">{{ ucfirst($cpmk->classroom?->period_type ?? '') }} {{ $cpmk->classroom?->academic_year ?? '' }}</div>
                            </td>
                            <td>
                                @if($cpmk->cpl)
                                    <span class="badge bg-light text-dark border">{{ $cpmk->cpl->code }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="small">
                                {{ $cpmk->creator?->name ?? '—' }}
                                <div class="text-muted" style="font-family:monospace; font-size:.7rem;">{{ $cpmk->creator?->identity ?? '' }}</div>
                            </td>
                            <td class="text-center fw-bold">{{ number_format($cpmk->percentage, 0) }}%</td>
                            <td class="text-center">
                                <span class="badge" style="background:{{ $sc['bg'] }}; color:{{ $sc['fg'] }};">{{ $sc['label'] }}</span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('kaprodi.cpmk-approvals.show', $cpmk) }}" class="btn btn-sm btn-obe-red">Tinjau</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-5"><em>Tidak ada CPMK pada filter ini.</em></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-sidebar-layout>
