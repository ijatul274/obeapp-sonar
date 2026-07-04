<x-sidebar-layout :title="'Preview Layout Baru'" :header="'Contoh'">

    <div class="alert alert-warning border-0 shadow-sm mb-4" style="border-left: 4px solid var(--obe-red) !important;">
        <strong>Halaman pilot.</strong> Ini contoh tampilan sidebar OBE versi Bootstrap.
        Login sebagai role berbeda (admin / kaprodi / dosen / mahasiswa) untuk melihat menu yang berbeda.
        Setelah Anda setujui, semua halaman lain akan dimigrasi mengikuti layout ini.
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="obe-stat-card">
                <div class="obe-stat-card__label">Stat 1</div>
                <div class="obe-stat-card__value">12</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="obe-stat-card">
                <div class="obe-stat-card__label">Stat 2</div>
                <div class="obe-stat-card__value">34</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="obe-stat-card">
                <div class="obe-stat-card__label">Stat 3</div>
                <div class="obe-stat-card__value">56</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="obe-stat-card">
                <div class="obe-stat-card__label">Stat 4</div>
                <div class="obe-stat-card__value">78</div>
            </div>
        </div>
    </div>

    <div class="obe-card mb-4">
        <h5 class="mb-3">Contoh Tabel</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Contoh data baris pertama</td>
                        <td><span class="badge" style="background:var(--obe-red); color:#fff;">Aktif</span></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-secondary">Edit</button>
                            <button class="btn btn-sm btn-obe-red">Hapus</button>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Contoh data baris kedua</td>
                        <td><span class="badge bg-secondary">Draft</span></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-secondary">Edit</button>
                            <button class="btn btn-sm btn-obe-red">Hapus</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="obe-card">
        <h5 class="mb-3">Contoh Form</h5>
        <form>
            <div class="mb-3">
                <label class="form-label">Nama</label>
                <input type="text" class="form-control" placeholder="Masukkan nama">
            </div>
            <div class="mb-3">
                <label class="form-label">Pilihan</label>
                <select class="form-select">
                    <option>Pilih salah satu</option>
                    <option>Opsi A</option>
                    <option>Opsi B</option>
                </select>
            </div>
            <button type="button" class="btn btn-obe-red">Simpan</button>
            <button type="button" class="btn btn-outline-secondary">Batal</button>
        </form>
    </div>

</x-sidebar-layout>
