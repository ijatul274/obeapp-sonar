<x-sidebar-layout :title="'Edit CPMK'" :header="'Edit CPMK ' . $classroomCpmk->code">

    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <p class="text-muted small mb-0">
            {{ $classroom->course?->code }} — {{ $classroom->name }} · {{ ucfirst($classroom->period_type ?? '') }} {{ $classroom->academic_year ?? '' }}
        </p>
        <a href="{{ route('dosen.classrooms.show', $classroom) }}" class="btn btn-obe-outline btn-sm">&larr; Kembali</a>
    </div>

    @if($classroomCpmk->status === 'rejected' && $classroomCpmk->rejection_note)
        <div class="alert alert-danger mb-3">
            <div class="fw-bold mb-1">CPMK ini ditolak oleh Kaprodi:</div>
            <div>{{ $classroomCpmk->rejection_note }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0 ps-3">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>
    @endif

    @php
        $existingIndicators = $classroomCpmk->indicators->map(fn($i) => ['desc'=>$i->description,'pct'=>$i->percentage])->toArray();
        $existingIndicatorsJson = json_encode($existingIndicators ?: [['desc'=>'','pct'=>'']]);
    @endphp

    <form method="POST" action="{{ route('dosen.classroom-cpmks.update', $classroomCpmk) }}"
          x-data="{
              indicatorWeightType: '{{ old('indicator_weight_type', $classroomCpmk->indicators->isNotEmpty() ? 'manual' : 'otomatis') }}',
              indicators: {{ $existingIndicatorsJson }},
              addIndicator() { this.indicators.push({desc:'', pct:''}); },
              removeIndicator(i) { if (this.indicators.length > 1) this.indicators.splice(i,1); }
          }">
        @csrf @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <div class="obe-card">
                    <label class="form-label fw-semibold text-uppercase small" style="font-size:.7rem;">Kode CPMK <span class="text-danger">*</span></label>
                    <input type="text" name="code" value="{{ old('code', $classroomCpmk->code) }}" required class="form-control" style="font-family:monospace;">
                </div>
            </div>

            <div class="col-md-6">
                <div class="obe-card">
                    <label class="form-label fw-semibold text-uppercase small" style="font-size:.7rem;">Bobot (%) <span class="text-danger">*</span></label>
                    <input type="number" name="percentage" value="{{ old('percentage', $classroomCpmk->percentage) }}" required min="1" max="100" step="0.01" class="form-control">
                </div>
            </div>

            <div class="col-12">
                <div class="obe-card">
                    <label class="form-label fw-semibold text-uppercase small" style="font-size:.7rem;">CPL Terkait <span class="text-danger">*</span></label>
                    <select name="cpl_id" required class="form-select">
                        <option value="">— Pilih CPL —</option>
                        @foreach($cpls as $cpl)
                            <option value="{{ $cpl->id }}" {{ old('cpl_id', $classroomCpmk->cpl_id) == $cpl->id ? 'selected' : '' }}>
                                {{ $cpl->code }} — {{ Str::limit($cpl->description, 70) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-12">
                <div class="obe-card">
                    <label class="form-label fw-semibold text-uppercase small" style="font-size:.7rem;">Deskripsi CPMK <span class="text-danger">*</span></label>
                    <textarea name="description" rows="3" required class="form-control">{{ old('description', $classroomCpmk->description) }}</textarea>
                </div>
            </div>

            <div class="col-12">
                <div class="obe-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="obe-card__title mb-0">Indikator Pencapaian</h2>
                        <div class="d-flex align-items-center gap-2">
                            <label class="form-label small mb-0">Bobot:</label>
                            <select name="indicator_weight_type" x-model="indicatorWeightType" class="form-select form-select-sm" style="width:auto;">
                                <option value="otomatis">Otomatis (rata)</option>
                                <option value="manual">Manual</option>
                            </select>
                        </div>
                    </div>

                    <template x-for="(ind, i) in indicators" :key="i">
                        <div class="border rounded p-3 mb-2" style="background:var(--obe-bg);">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold" style="color:var(--obe-red);" x-text="'Indikator ' + (i+1)"></span>
                                <button type="button" @click="removeIndicator(i)" x-show="indicators.length > 1" class="btn btn-sm btn-obe-outline">Hapus</button>
                            </div>
                            <div class="d-flex gap-2">
                                <input type="text" :name="'indicator_descriptions[' + i + ']'" x-model="ind.desc" placeholder="Deskripsi indikator..." class="form-control form-control-sm flex-grow-1">
                                <input type="number" :name="'indicator_percentages[' + i + ']'" x-model="ind.pct" x-show="indicatorWeightType === 'manual'" placeholder="%" min="0" max="100" step="0.01" class="form-control form-control-sm text-center" style="width:90px;">
                            </div>
                        </div>
                    </template>

                    <button type="button" @click="addIndicator()" class="btn btn-obe-outline btn-sm w-100 d-inline-flex align-items-center justify-content-center gap-2 border-dashed">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Tambah Indikator
                    </button>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-3">
            <button type="submit" name="submit_action" value="draft" class="btn btn-obe-outline flex-grow-1">Simpan sebagai Draft</button>
            <button type="submit" name="submit_action" value="submit" class="btn btn-obe-red flex-grow-1">Simpan & Kirim untuk Persetujuan</button>
        </div>
    </form>
</x-sidebar-layout>
