<x-sidebar-layout :title="'Tinjauan CPMK'" :header="'Tinjauan CPMK ' . $classroomCpmk->code">

    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <div>
            <p class="text-muted small mb-0">
                {{ $classroomCpmk->classroom?->course?->code }} — {{ $classroomCpmk->classroom?->name }}
                · {{ ucfirst($classroomCpmk->classroom?->period_type ?? '') }} {{ $classroomCpmk->classroom?->academic_year ?? '' }}
            </p>
        </div>
        <a href="{{ route('kaprodi.cpmk-approvals.index') }}" class="btn btn-obe-outline btn-sm">&larr; Kembali</a>
    </div>

    <div class="row g-3">
        {{-- LEFT --}}
        <div class="col-lg-8">
            @php
                $sc = match($classroomCpmk->status) {
                    'pending'  => ['bg'=>'#fef3c7','fg'=>'#92400e','label'=>'Menunggu Persetujuan'],
                    'approved' => ['bg'=>'#d1fae5','fg'=>'#065f46','label'=>'Disetujui'],
                    'rejected' => ['bg'=>'#fee2e2','fg'=>'#991b1b','label'=>'Ditolak'],
                    default    => ['bg'=>'#e5e7eb','fg'=>'#374151','label'=>'Draft'],
                };
            @endphp

            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="badge px-3 py-2" style="background:{{ $sc['bg'] }}; color:{{ $sc['fg'] }};">{{ $sc['label'] }}</span>
                @if($classroomCpmk->approved_at)
                    <span class="text-muted small">{{ $classroomCpmk->approved_at->format('d M Y H:i') }}</span>
                @endif
            </div>

            <div class="obe-card mb-3">
                <h2 class="obe-card__title mb-3">Detail CPMK</h2>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="text-muted small text-uppercase fw-semibold mb-1" style="font-size:.7rem;">Kode</div>
                        <div class="fw-bold" style="font-family:monospace;">{{ $classroomCpmk->code }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small text-uppercase fw-semibold mb-1" style="font-size:.7rem;">Bobot</div>
                        <div class="fw-bold">{{ number_format($classroomCpmk->percentage, 0) }}%</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small text-uppercase fw-semibold mb-1" style="font-size:.7rem;">CPL Terkait</div>
                        <div class="fw-bold" style="color:var(--obe-red);">{{ $classroomCpmk->cpl?->code ?? '—' }}</div>
                        <div class="text-muted small">{{ Str::limit($classroomCpmk->cpl?->description ?? '', 80) }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small text-uppercase fw-semibold mb-1" style="font-size:.7rem;">Dosen Pembuat</div>
                        <div class="fw-semibold">{{ $classroomCpmk->creator?->name ?? '—' }}</div>
                        <div class="text-muted small" style="font-family:monospace;">{{ $classroomCpmk->creator?->identity ?? '' }}</div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small text-uppercase fw-semibold mb-1" style="font-size:.7rem;">Deskripsi</div>
                        <div class="p-3 border rounded" style="background:var(--obe-bg);">{{ $classroomCpmk->description }}</div>
                    </div>
                </div>
            </div>

            @if($classroomCpmk->indicators->isNotEmpty())
                <div class="obe-card mb-3">
                    <h2 class="obe-card__title mb-3">Indikator Pencapaian</h2>
                    <div class="list-group list-group-flush">
                        @foreach($classroomCpmk->indicators as $i => $ind)
                            <div class="list-group-item px-0">
                                <div class="d-flex gap-2 align-items-start">
                                    <span class="badge rounded-pill" style="background:var(--obe-red); color:#fff;">{{ $i+1 }}</span>
                                    <div class="flex-grow-1">
                                        <div>{{ $ind->description }}</div>
                                        @if($ind->assessments->isNotEmpty())
                                            <div class="d-flex flex-wrap gap-1 mt-2">
                                                @foreach($ind->assessments as $a)
                                                    <span class="badge bg-light text-dark border">{{ $a->name }} <small class="text-muted">({{ number_format($a->percentage, 0) }}%)</small></span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <span class="fw-bold" style="color:var(--obe-red);">{{ number_format($ind->percentage, 0) }}%</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($classroomCpmk->status === 'rejected' && $classroomCpmk->rejection_note)
                <div class="alert alert-danger">
                    <div class="fw-bold text-uppercase small mb-2" style="letter-spacing:.05em;">Catatan Penolakan</div>
                    {{ $classroomCpmk->rejection_note }}
                </div>
            @endif

            @if($classroomCpmk->status === 'pending')
                <div class="obe-card">
                    <h2 class="obe-card__title mb-3">Keputusan Persetujuan</h2>

                    <form method="POST" action="{{ route('kaprodi.cpmk-approvals.approve', $classroomCpmk) }}" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('Setujui CPMK {{ $classroomCpmk->code }}?');">
                            ✓ Setujui CPMK
                        </button>
                    </form>

                    <div x-data="{ showReject: false }">
                        <button @click="showReject = !showReject" type="button" class="btn btn-obe-red w-100">
                            Tolak & Minta Revisi
                        </button>
                        <div x-show="showReject" x-transition style="display:none;" class="mt-3">
                            <form method="POST" action="{{ route('kaprodi.cpmk-approvals.reject', $classroomCpmk) }}">
                                @csrf
                                <label class="form-label fw-semibold">Catatan untuk Dosen <span class="text-danger">*</span></label>
                                <textarea name="rejection_note" rows="4" required minlength="5" class="form-control mb-2"
                                          placeholder="Tuliskan alasan penolakan dan saran perbaikan...">{{ old('rejection_note') }}</textarea>
                                @error('rejection_note')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                                <button type="submit" class="btn btn-obe-red w-100">Kirim Penolakan</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- RIGHT sidebar --}}
        <div class="col-lg-4">
            <div class="obe-card mb-3">
                <h2 class="obe-card__title mb-3">Template CPMK (Referensi)</h2>
                <div class="list-group list-group-flush">
                    @forelse($templates as $tmpl)
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <span class="fw-bold" style="font-family:monospace;">{{ $tmpl->code }}</span>
                                    @if($tmpl->cpl)
                                        <span class="badge bg-light text-dark border ms-1">{{ $tmpl->cpl->code }}</span>
                                    @endif
                                    <div class="text-muted small mt-1">{{ Str::limit($tmpl->description, 80) }}</div>
                                </div>
                                <span class="fw-bold small text-muted">{{ number_format($tmpl->percentage, 0) }}%</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small fst-italic mb-0">Belum ada template untuk mata kuliah ini.</p>
                    @endforelse
                </div>
            </div>

            <div class="obe-card">
                <h2 class="obe-card__title mb-3">Informasi Kelas</h2>
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted fw-normal">Kelas</dt>
                    <dd class="col-7 fw-semibold">{{ $classroomCpmk->classroom?->name }}</dd>
                    <dt class="col-5 text-muted fw-normal">Mata Kuliah</dt>
                    <dd class="col-7 fw-semibold">{{ $classroomCpmk->classroom?->course?->code }}</dd>
                    <dt class="col-5 text-muted fw-normal">Periode</dt>
                    <dd class="col-7 fw-semibold">{{ ucfirst($classroomCpmk->classroom?->period_type ?? '') }} {{ $classroomCpmk->classroom?->academic_year ?? '' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Diajukan</dt>
                    <dd class="col-7 fw-semibold">{{ $classroomCpmk->updated_at->format('d M Y') }}</dd>
                </dl>
            </div>
        </div>
    </div>

</x-sidebar-layout>
