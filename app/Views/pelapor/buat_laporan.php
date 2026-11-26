<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<style>
    #map {
        height: 360px;
        width: 100%;
        border-radius: 12px;
        border: 1px solid #dee2e6;
        margin-top: 12px;
        z-index: 1;
    }

    .form-card {
        background: #ffffff;
        padding: 28px;
        border-radius: 14px;
        border: 1px solid #e9ecef;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
    }

    .section-box {
        background: #f8faf8;
        border: 1px solid #e3ece5;
        border-radius: 12px;
        padding: 20px;
        height: 100%;
    }

    .section-badge {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
    }

    .upload-box {
        background: #ffffff;
        border: 1.5px dashed #adb5bd;
        border-radius: 10px;
        padding: 16px;
        transition: border-color 0.2s ease, background-color 0.2s ease;
    }

    .upload-box:hover {
        border-color: #198754;
        background-color: #f8fcf9;
    }

    .location-info {
        background: #ffffff;
        border: 1px solid #dee2e6;
        padding: 16px;
        border-radius: 10px;
        margin-top: 14px;
    }

    .location-item {
        background: #f5f7f5;
        border-radius: 8px;
        padding: 10px 12px;
        height: 100%;
    }

    @media (max-width: 576px) {
        .form-card {
            padding: 18px;
        }

        .section-box {
            padding: 15px;
        }

        #map {
            height: 280px;
        }
    }
</style>

<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid px-lg-4">

    <div class="content-card mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-success-subtle text-success rounded-3 p-3 d-none d-sm-flex align-items-center justify-content-center">
                    <i class="bi bi-plus-circle-fill fs-3"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-1">Buat Laporan Kebersihan</h3>
                    <p class="text-muted mb-0">
                        Laporkan masalah kebersihan di lingkungan kampus beserta foto kondisi dan titik lokasi.
                    </p>
                </div>
            </div>

            <a href="/pelapor/dashboard" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    <div class="form-card">

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div><?= esc(session()->getFlashdata('error')) ?></div>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill"></i>
                <div><?= esc(session()->getFlashdata('success')) ?></div>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <div class="fw-semibold mb-1">
                    <i class="bi bi-exclamation-circle-fill me-1"></i>
                    Periksa kembali isian formulir berikut:
                </div>
                <ul class="mb-0 ps-3">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div id="pesanOffline" class="alert alert-info d-none d-flex align-items-center gap-2">
            <i class="bi bi-wifi-off fs-5"></i>
            <div>
                Kamu sedang offline. Laporan akan disimpan sementara di perangkat dan disinkronkan otomatis saat online.
            </div>
        </div>

        <div id="pesanSinkronisasi" class="alert alert-success d-none"></div>

        <form action="/pelapor/laporan/simpan"
              method="post"
              enctype="multipart/form-data"
              id="formLaporan">

            <?= csrf_field() ?>

            <div class="row g-4">

                <!-- Kolom Kiri: Detail & Bukti Foto Laporan -->
                <div class="col-lg-6">
                    <div class="section-box">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="section-badge bg-success-subtle text-success">
                                <i class="bi bi-file-earmark-text"></i>
                            </span>
                            <div>
                                <h5 class="fw-bold mb-0">Informasi Laporan</h5>
                                <small class="text-muted">Lengkapi detail masalah dan unggah foto kondisi.</small>
                            </div>
                        </div>

                        <hr class="mt-2 mb-3">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-card-heading text-success me-1"></i>
                                Judul Laporan <span class="text-danger">*</span>
                            </label>

                            <input type="text"
                                   name="judul"
                                   class="form-control"
                                   placeholder="Contoh: Sampah menumpuk di depan kelas"
                                   value="<?= esc(old('judul') ?? '') ?>"
                                   required
                                   minlength="5"
                                   maxlength="150">
                            <div class="form-text">Minimal 5 karakter, maksimal 150 karakter.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-tags text-success me-1"></i>
                                Kategori <span class="text-danger">*</span>
                            </label>

                            <select name="kategori" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>

                                <?php
                                $kategoriPilihan = [
                                    'Sampah',
                                    'Saluran Air',
                                    'Toilet',
                                    'Ruang Kelas',
                                    'Lingkungan',
                                    'Lainnya'
                                ];
                                ?>

                                <?php foreach ($kategoriPilihan as $kategori): ?>
                                    <option value="<?= esc($kategori) ?>"
                                        <?= old('kategori') === $kategori ? 'selected' : '' ?>>
                                        <?= esc($kategori) ?>
                                    </option>
                                <?php endforeach; ?>

                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-text-paragraph text-success me-1"></i>
                                Deskripsi Kondisi <span class="text-danger">*</span>
                            </label>

                            <textarea name="deskripsi"
                                      class="form-control"
                                      rows="5"
                                      placeholder="Jelaskan kondisi kebersihan, tingkat keparahan, dan patokan lokasi..."
                                      required
                                      minlength="10"><?= esc(old('deskripsi') ?? '') ?></textarea>
                            <div class="form-text">Tuliskan penjelasan minimal 10 karakter agar petugas mudah menindaklanjuti.</div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-camera text-success me-1"></i>
                                Foto Kondisi <span class="text-danger">*</span>
                            </label>

                            <div class="upload-box">
                                <input type="file"
                                       name="foto"
                                       id="foto"
                                       class="form-control"
                                       accept="image/jpeg,image/png,image/webp"
                                       capture="environment"
                                       required>

                                <div class="d-flex align-items-center gap-2 mt-2 text-muted small">
                                    <i class="bi bi-info-circle text-success"></i>
                                    <span>Gunakan foto JPG, PNG, atau WEBP dengan ukuran maksimal 2 MB.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Pengambilan Lokasi & Peta -->
                <div class="col-lg-6">
                    <div class="section-box">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="section-badge bg-success-subtle text-success">
                                    <i class="bi bi-geo-alt-fill"></i>
                                </span>
                                <div>
                                    <h5 class="fw-bold mb-0">Lokasi Laporan</h5>
                                    <small class="text-muted">Gunakan GPS atau klik langsung titik pada peta.</small>
                                </div>
                            </div>

                            <button type="button"
                                    class="btn btn-success btn-sm px-3 py-2"
                                    onclick="ambilLokasi()">
                                <i class="bi bi-crosshair me-1"></i>
                                Ambil Lokasi Saya
                            </button>
                        </div>

                        <hr class="mt-2 mb-3">

                        <div id="statusLokasi" class="small text-muted bg-white border rounded-2 px-3 py-2">
                            <i class="bi bi-pin-map me-1 text-success"></i>
                            Lokasi belum dipilih.
                        </div>

                        <div id="map"></div>

                        <div class="location-info">
                            <div class="row g-2 small">
                                <div class="col-12">
                                    <div class="location-item">
                                        <div class="text-muted fw-semibold mb-1">
                                            <i class="bi bi-signpost-2 text-success me-1"></i>
                                            Nama Lokasi
                                        </div>
                                        <div id="namaLokasiTampil" class="fw-medium text-dark">Belum diketahui</div>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="location-item">
                                        <div class="text-muted fw-semibold mb-1">Latitude</div>
                                        <div id="latitudeTampil" class="fw-medium text-dark text-break">-</div>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="location-item">
                                        <div class="text-muted fw-semibold mb-1">Longitude</div>
                                        <div id="longitudeTampil" class="fw-medium text-dark text-break">-</div>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="location-item">
                                        <div class="text-muted fw-semibold mb-1">Akurasi GPS</div>
                                        <div id="akurasiTampil" class="fw-medium text-dark">Belum tersedia</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <input type="hidden" name="latitude" id="latitude"
                   value="<?= esc(old('latitude') ?? '') ?>">

            <input type="hidden" name="longitude" id="longitude"
                   value="<?= esc(old('longitude') ?? '') ?>">

            <input type="hidden" name="nama_lokasi" id="nama_lokasi"
                   value="<?= esc(old('nama_lokasi') ?? '') ?>">

            <input type="hidden" name="akurasi_gps" id="akurasi_gps"
                   value="<?= esc(old('akurasi_gps') ?? '') ?>">

            <hr class="my-4">

            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                <small class="text-muted">
                    <i class="bi bi-shield-check text-success me-1"></i>
                    Pastikan foto dan titik lokasi sudah sesuai sebelum mengirim laporan.
                </small>

                <div class="d-flex justify-content-end gap-2">
                    <a href="/pelapor/dashboard" class="btn btn-outline-secondary px-4">
                        <i class="bi bi-x-circle me-1"></i>
                        Batal
                    </a>

                    <button type="submit" class="btn btn-success px-4" id="tombolKirim">
                        <i class="bi bi-send me-1"></i>
                        Kirim Laporan
                    </button>
                </div>
            </div>

        </form>

    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="/offline-storage.js"></script>
<script src="/offline-sync.js"></script>

<script>
    let marker;

    const map = L.map('map').setView([-7.8, 110.4], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);


    function tampilkanMarker(lat, lng, akurasi = '') {

        if (marker) {
            map.removeLayer(marker);
        }

        marker = L.marker([lat, lng], {
            draggable: true
        }).addTo(map);

        marker.on('dragend', function (event) {
            const posisi = event.target.getLatLng();
            aturLokasi(posisi.lat, posisi.lng, '');
        });

        map.setView([lat, lng], 17);

        aturLokasi(lat, lng, akurasi);
    }


    async function aturLokasi(lat, lng, akurasi = '') {

        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;

        document.getElementById('latitudeTampil').textContent = lat;
        document.getElementById('longitudeTampil').textContent = lng;

        document.getElementById('akurasi_gps').value = akurasi;

        document.getElementById('akurasiTampil').textContent =
            akurasi !== ''
                ? Number(akurasi).toFixed(2) + ' meter'
                : 'Tidak tersedia (lokasi manual)';

        document.getElementById('statusLokasi').textContent =
            'Koordinat lokasi berhasil dipilih.';

        document.getElementById('nama_lokasi').value = '';

        document.getElementById('namaLokasiTampil').textContent =
            'Mencari nama lokasi...';

        try {

            const response = await fetch(
                'https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=' +
                encodeURIComponent(lat) +
                '&lon=' +
                encodeURIComponent(lng)
            );

            if (!response.ok) {
                throw new Error('Gagal mengambil nama lokasi.');
            }

            const data = await response.json();

            const nama = data.display_name || 'Lokasi tidak diketahui';

            document.getElementById('nama_lokasi').value = nama;
            document.getElementById('namaLokasiTampil').textContent = nama;

        } catch (error) {

            document.getElementById('nama_lokasi').value = '';

            document.getElementById('namaLokasiTampil').textContent =
                'Nama lokasi belum tersedia.';

        }
    }


    function ambilLokasi() {

        const status = document.getElementById('statusLokasi');

        if (!navigator.geolocation) {
            status.textContent = 'Browser tidak mendukung GPS.';
            return;
        }

        status.textContent = 'Sedang mengambil lokasi...';

        navigator.geolocation.getCurrentPosition(

            function (position) {

                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const akurasi = position.coords.accuracy;

                tampilkanMarker(lat, lng, akurasi);

            },

            function (error) {

                status.textContent =
                    'Gagal mengambil lokasi. Pastikan izin GPS sudah diberikan.';

            },

            {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0
            }

        );
    }


    map.on('click', function (event) {
        tampilkanMarker(event.latlng.lat, event.latlng.lng, '');
    });


    // Memulihkan lokasi lama setelah validasi gagal.
    const latitudeLama = document.getElementById('latitude').value;
    const longitudeLama = document.getElementById('longitude').value;
    const namaLokasiLama = document.getElementById('nama_lokasi').value;
    const akurasiLama = document.getElementById('akurasi_gps').value;

    if (latitudeLama && longitudeLama) {

        tampilkanMarker(
            parseFloat(latitudeLama),
            parseFloat(longitudeLama),
            akurasiLama
        );

        if (namaLokasiLama) {
            document.getElementById('nama_lokasi').value = namaLokasiLama;
            document.getElementById('namaLokasiTampil').textContent = namaLokasiLama;
        }
    }


    function perbaruiStatusKoneksi() {

        const pesan = document.getElementById('pesanOffline');

        pesan.classList.toggle('d-none', navigator.onLine === true);
    }

    window.addEventListener('online', perbaruiStatusKoneksi);
    window.addEventListener('offline', perbaruiStatusKoneksi);

    perbaruiStatusKoneksi();


    // Penyimpanan laporan ketika offline.
    document.getElementById('formLaporan').addEventListener('submit', async function (event) {

        if (navigator.onLine) {
            return;
        }

        event.preventDefault();

        const form = event.target;
        const tombol = document.getElementById('tombolKirim');
        const fileFoto = document.getElementById('foto').files[0];

        if (!form.reportValidity()) {
            return;
        }

        if (!fileFoto) {
            alert('Silakan pilih foto laporan terlebih dahulu.');
            return;
        }

        if (fileFoto.size > 2 * 1024 * 1024) {
            alert('Ukuran foto maksimal 2 MB.');
            return;
        }

        const latitude = document.getElementById('latitude').value;
        const longitude = document.getElementById('longitude').value;
        const akurasi = document.getElementById('akurasi_gps').value;

        if (!latitude || !longitude) {
            alert('Silakan ambil lokasi GPS atau pilih titik pada peta.');
            return;
        }

        tombol.disabled = true;
        tombol.textContent = 'Menyimpan laporan...';

        try {

            const dataLaporan = {
                judul: form.querySelector('[name="judul"]').value,
                kategori: form.querySelector('[name="kategori"]').value,
                deskripsi: form.querySelector('[name="deskripsi"]').value,
                foto: fileFoto,
                latitude: latitude,
                longitude: longitude,
                nama_lokasi: document.getElementById('nama_lokasi').value,
                akurasi_gps: akurasi || null
            };

            const id = await simpanLaporanOffline(dataLaporan);

            alert(
                'Laporan berhasil disimpan sementara di perangkat dengan nomor antrean ' +
                id +
                '. Laporan akan disinkronkan ketika internet tersedia dan halaman aplikasi dibuka.'
            );

            form.reset();

        } catch (error) {

            console.error(error);

            alert(
                'Gagal menyimpan laporan offline. Pastikan browser mendukung penyimpanan lokal.'
            );

        } finally {

            tombol.disabled = false;

            tombol.innerHTML =
                '<i class="bi bi-send me-1"></i> Kirim Laporan';

        }

    });
</script>

<?= $this->endSection() ?>