<x-sidebar-layout :title="'Kelola Kelas'" :header="$classroom->name">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-2">
        <div>
            <p class="small mb-0 text-muted text-uppercase fw-semibold" style="letter-spacing:.05em;">
                Kelas: {{ $classroom->name }} · Kode <span style="font-family:monospace;">{{ $classroom->enrollment_code }}</span>
            </p>
            <h2 class="h6 fw-bold mb-0">{{ $course->code }} — {{ $course->name }}</h2>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('dosen.classrooms.report', $classroom) }}" class="btn btn-obe-red btn-sm">Laporan Nilai</a>
            <a href="{{ route('dosen.dashboard') }}" class="btn btn-obe-outline btn-sm">&larr; Dashboard</a>
        </div>
    </div>

    <div class="obe-card mb-3">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="text-muted small text-uppercase fw-semibold mb-1" style="font-size:.7rem;">Kode MK</div>
                <div class="fw-bold" style="font-family:monospace;">{{ $course->code }}</div>
            </div>
            <div class="col-6 col-md-6">
                <div class="text-muted small text-uppercase fw-semibold mb-1" style="font-size:.7rem;">Nama MK</div>
                <div class="fw-semibold">{{ $course->name }}</div>
            </div>
            <div class="col-3 col-md-1">
                <div class="text-muted small text-uppercase fw-semibold mb-1" style="font-size:.7rem;">SKS</div>
                <div class="fw-bold">{{ $course->sks }}</div>
            </div>
            <div class="col-3 col-md-2">
                <div class="text-muted small text-uppercase fw-semibold mb-1" style="font-size:.7rem;">Semester</div>
                <div class="fw-bold">{{ $course->semester }}</div>
            </div>
        </div>
        @if($course->cpls?->count() > 0)
            <div class="mt-3 pt-3 border-top">
                <div class="text-muted small text-uppercase fw-semibold mb-2" style="font-size:.7rem;">CPL Didukung</div>
                <div class="d-flex flex-wrap gap-1">
                    @foreach($course->cpls as $cpl)
                        <span class="badge bg-light text-dark border">{{ $cpl->code }}</span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <h2 class="h6 fw-bold mb-3" style="border-left:3px solid var(--obe-red); padding-left:.6rem;">
        CPMK & Komponen Penilaian
        <small class="text-muted fw-normal ms-2">{{ ucfirst($classroom->period_type ?? '') }} {{ $classroom->academic_year ?? '' }}</small>
    </h2>

    @forelse($cpmks as $cpmk)
        <div class="obe-card mb-3 p-0 overflow-hidden">
            <div class="px-3 py-3 d-flex flex-wrap align-items-start gap-2 border-bottom" style="background:var(--obe-bg);">
                <span class="badge" style="background:var(--obe-red); color:#fff; font-family:monospace;">{{ $cpmk->code }}</span>
                <div class="flex-grow-1 ms-2">
                    <div class="fw-semibold small">{{ $cpmk->description }}</div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                        <span class="text-muted small">Bobot: <span class="fw-bold" style="color:var(--obe-red);">{{ $cpmk->percentage }}%</span></span>
                        @if($cpmk->cpl)<span class="badge bg-light text-dark border">{{ $cpmk->cpl->code }}</span>@endif
                        @if($cpmk->meeting_start && $cpmk->meeting_end)
                            <span class="text-muted small">Pertemuan {{ $cpmk->meeting_start }}–{{ $cpmk->meeting_end }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="p-3">
                <div class="text-muted small text-uppercase fw-semibold mb-3" style="font-size:.7rem;">Sub-CPMK, Indikator & Komponen Penilaian</div>
                @forelse($cpmk->subCpmks as $sub)
                    @php
                        $compRows = $sub->indicators->flatMap(fn ($i) => $i->assessments
                            ->where('classroom_id', $classroom->id)
                            ->map(fn ($a) => [
                                'nama' => $a->name,
                                'deskripsi' => $a->description ?? '',
                                'indicator_id' => $a->indicator_id,
                                'bobotType' => $a->is_auto ? 'otomatis' : 'manual',
                                'bobot' => $a->is_auto ? '' : (float) $a->percentage,
                            ]))->values();
                        $indOptions = $sub->indicators->map(fn ($i) => ['id' => $i->id, 'description' => $i->description])->values();
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2 px-2 py-2 rounded gap-2" style="background:var(--obe-bg);">
                            <span class="fw-semibold small"><span class="badge bg-success me-1">Sub-CPMK {{ $loop->iteration }}</span>{{ $sub->description }}</span>
                            <span class="d-flex align-items-center gap-1">
                                @if($sub->meetings)<span class="badge bg-light text-dark border">{{ $sub->meetings }}× pertemuan</span>@endif
                                <span class="badge bg-success">{{ number_format($sub->percentage, 1) }}%</span>
                                @if($sub->indicators->count() > 0)
                                    <button type="button" class="btn btn-sm btn-obe-red py-0 px-2" data-bs-toggle="modal" data-bs-target="#modalKomponenSub{{ $sub->id }}" title="Tambah/atur komponen penilaian untuk Sub-CPMK ini">
                                        Kelola Komponen
                                    </button>
                                @endif
                            </span>
                        </div>
                        @if($sub->indicators->count() > 0)
                            @foreach($sub->indicators as $indicator)
                        <div class="border rounded p-3 mb-2" style="background:var(--obe-bg);">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div class="d-flex gap-2 flex-grow-1">
                                    <span class="badge rounded-pill flex-shrink-0" style="background:var(--obe-red-soft); color:var(--obe-red);">{{ $loop->iteration }}</span>
                                    <div class="flex-grow-1">
                                        <div class="small fw-semibold">{{ $indicator->description }}</div>

                                        @if($indicator->assessments->where('classroom_id', $classroom->id)->count() > 0)
                                            <div class="text-muted small mt-2 mb-1" style="font-size:.72rem;">
                                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-1px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12H3m0 0l4-4m-4 4l4 4"/></svg>
                                                Klik komponen di bawah untuk input nilai mahasiswa
                                            </div>
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($indicator->assessments->where('classroom_id', $classroom->id) as $a)
                                                    <a href="{{ route('assessments.scores.index', $a) }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 obe-component-btn"
                                                       title="Klik untuk input nilai mahasiswa pada komponen ini">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                        <span>{{ $a->name }}</span>
                                                        <span class="badge" style="background:var(--obe-red); color:#fff;">{{ number_format($a->percentage, 1) }}%</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="small mt-1">
                                                <span class="badge" style="background:var(--obe-red-soft); color:var(--obe-red); font-size:.72rem;">
                                                    Belum ada komponen — klik "Kelola Komponen" di atas, pilih indikator ini
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                            @endforeach
                        @else
                            <p class="text-muted small fst-italic mb-0 ps-2">Belum ada indikator pada Sub-CPMK ini.</p>
                        @endif

                        @if($sub->indicators->count() > 0)
                        {{-- Modal: Kelola Komponen (per Sub-CPMK). Tiap komponen pilih indikator + bobot; bobot indikator diturunkan dari sini. --}}
                        <div class="modal fade" id="modalKomponenSub{{ $sub->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header">
                                        <div>
                                            <h5 class="modal-title fw-bold mb-1">Komponen Penilaian</h5>
                                            <div class="d-flex flex-wrap gap-2 small text-muted">
                                                <span class="badge bg-light text-dark border">{{ $cpmk->code }}</span>
                                                <span>Sub-CPMK: {{ Str::limit($sub->description, 50) }}</span>
                                            </div>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('dosen.subcpmk.components.store', [$classroom, $sub]) }}" method="POST"
                                          x-data="{
                                              indicators: {{ json_encode($indOptions) }},
                                              components: {{ json_encode($compRows) }},
                                              newRow: { nama:'', indicator_id:'', bobot:'' },
                                              get manualTotal(){ return this.components.filter(c=>c.bobotType==='manual').reduce((s,c)=>s+parseFloat(c.bobot||0),0); },
                                              get autoCount(){ return this.components.filter(c=>c.bobotType==='otomatis').length; },
                                              get autoBobot(){ return this.autoCount===0?0:(100-this.manualTotal)/this.autoCount; },
                                              get totalBobot(){ return this.manualTotal+this.autoCount*this.autoBobot; },
                                              get isValid(){ return this.components.length>0 && Math.abs(this.totalBobot-100)<0.1 && this.components.every(c=>String(c.indicator_id)!=='' && c.indicator_id!==null); },
                                              effBobot(c){ return c.bobotType==='manual'?parseFloat(c.bobot||0):this.autoBobot; },
                                              pct(c){ return this.effBobot(c).toFixed(1)+'%'+(c.bobotType==='otomatis'?' (auto)':''); },
                                              indWeight(id){ return this.components.filter(c=>String(c.indicator_id)===String(id)).reduce((s,c)=>s+this.effBobot(c),0); },
                                              addRow(){ if(!this.newRow.nama.trim()||String(this.newRow.indicator_id)==='')return; const b=this.newRow.bobot; const isM=b!==''&&b!==null&&String(b).trim()!==''; this.components.push({nama:this.newRow.nama,deskripsi:'',indicator_id:this.newRow.indicator_id,bobotType:isM?'manual':'otomatis',bobot:isM?b:null}); this.newRow={nama:'',indicator_id:'',bobot:''}; },
                                              removeRow(i){ this.components.splice(i,1); }
                                          }">
                                        @csrf
                                        <template x-for="(c,i) in components" :key="i">
                                            <div>
                                                <input type="hidden" :name="'components['+i+'][nama]'" :value="c.nama">
                                                <input type="hidden" :name="'components['+i+'][deskripsi]'" :value="c.deskripsi">
                                                <input type="hidden" :name="'components['+i+'][indicator_id]'" :value="c.indicator_id">
                                                <input type="hidden" :name="'components['+i+'][bobotType]'" :value="c.bobotType">
                                                <input type="hidden" :name="'components['+i+'][bobot]'" :value="c.bobot">
                                            </div>
                                        </template>
                                        <div class="modal-body">
                                            <div x-show="components.length>0" class="alert d-flex justify-content-between mb-3" :class="isValid?'alert-success':'alert-warning'">
                                                <span class="small fw-semibold" x-text="isValid?'✓ Total 100% — siap disimpan':'Total: '+totalBobot.toFixed(1)+'% (harus 100%, tiap komponen wajib pilih indikator)'"></span>
                                            </div>
                                            <p class="text-muted small mb-2">Tambahkan komponen (tugas, kuis, UTS, …). Untuk tiap komponen <strong>pilih indikator</strong> yang diukur lalu isi <strong>bobot</strong>. Bobot kosong = dibagi rata; total komponen Sub-CPMK ini harus 100%. <strong>Bobot indikator dihitung otomatis</strong> = jumlah bobot komponennya.</p>
                                            <div class="table-responsive">
                                                <table class="table table-sm align-middle mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th class="text-center" style="width:36px;">#</th>
                                                            <th>Nama Komponen</th>
                                                            <th style="width:210px;">Indikator</th>
                                                            <th class="text-center" style="width:110px;">Bobot (%)</th>
                                                            <th class="text-center" style="width:70px;">Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <template x-for="(c,i) in components" :key="i">
                                                            <tr>
                                                                <td class="text-center text-muted small" x-text="i+1"></td>
                                                                <td><input type="text" x-model="c.nama" class="form-control form-control-sm" placeholder="Nama komponen"></td>
                                                                <td>
                                                                    <select @change="c.indicator_id = $event.target.value" class="form-select form-select-sm" :class="String(c.indicator_id)===''?'border-danger':''">
                                                                        <option value="" :selected="String(c.indicator_id)===''">— pilih indikator —</option>
                                                                        <template x-for="ind in indicators" :key="ind.id">
                                                                            <option :value="ind.id" :selected="String(ind.id)===String(c.indicator_id)" x-text="ind.description"></option>
                                                                        </template>
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <input type="number" x-model="c.bobot"
                                                                           @input="c.bobotType = (c.bobot !== '' && c.bobot !== null && String(c.bobot).trim() !== '') ? 'manual' : 'otomatis'"
                                                                           placeholder="auto" min="0" max="100" step="0.01" class="form-control form-control-sm text-center">
                                                                    <div class="text-center mt-1" style="font-size:.65rem; color:var(--obe-red); font-weight:600;" x-text="pct(c)"></div>
                                                                </td>
                                                                <td class="text-center"><button type="button" class="btn btn-sm btn-obe-red" @click="removeRow(i)">Hapus</button></td>
                                                            </tr>
                                                        </template>
                                                        <tr x-show="components.length===0"><td colspan="5" class="text-center py-3 text-muted small">Belum ada komponen.</td></tr>
                                                        <tr style="background:var(--obe-red-soft);">
                                                            <td class="text-center fw-bold" style="color:var(--obe-red);">+</td>
                                                            <td><input type="text" x-model="newRow.nama" @keydown.enter.prevent="addRow()" placeholder="Nama komponen..." class="form-control form-control-sm"></td>
                                                            <td>
                                                                <select @change="newRow.indicator_id = $event.target.value" class="form-select form-select-sm">
                                                                    <option value="" :selected="String(newRow.indicator_id)===''">— pilih indikator —</option>
                                                                    <template x-for="ind in indicators" :key="ind.id">
                                                                        <option :value="ind.id" :selected="String(ind.id)===String(newRow.indicator_id)" x-text="ind.description"></option>
                                                                    </template>
                                                                </select>
                                                            </td>
                                                            <td><input type="number" x-model="newRow.bobot" @keydown.enter.prevent="addRow()" placeholder="auto" min="0" max="100" class="form-control form-control-sm text-center"></td>
                                                            <td class="text-center"><button type="button" @click="addRow()" class="btn btn-sm btn-obe-red">Add</button></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="mt-3">
                                                <div class="text-muted small fw-semibold mb-1">Bobot indikator (otomatis dari komponen):</div>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <template x-for="ind in indicators" :key="ind.id">
                                                        <span class="badge bg-light text-dark border">
                                                            <span x-text="ind.description.length>34?ind.description.slice(0,34)+'…':ind.description"></span>:
                                                            <span class="fw-bold" style="color:var(--obe-red);" x-text="indWeight(ind.id).toFixed(1)+'%'"></span>
                                                        </span>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-obe-outline" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" :disabled="!isValid" :class="isValid?'':'disabled'" class="btn btn-obe-red">Simpan Komponen</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                @empty
                    <p class="text-muted small fst-italic mb-0">Belum ada Sub-CPMK untuk CPMK ini.</p>
                @endforelse
            </div>
        </div>
    @empty
        <div class="text-center py-5 border border-dashed rounded">
            <p class="text-muted mb-1 fw-semibold">Belum ada CPMK</p>
            <small class="text-muted">CPMK untuk mata kuliah ini belum ditetapkan oleh Kaprodi.</small>
        </div>
    @endforelse

</x-sidebar-layout>
