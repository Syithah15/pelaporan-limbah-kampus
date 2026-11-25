<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<style>
    #map {
        height: 350px;
        width: 100%;
        border-radius: 10px;
        margin-top: 12px;
    }

    .form-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .location-info {
        background: #f5f7f5;
        padding: 15px;
        border-radius: 8px;
        margin-top: 12px;
    }
</style>

<?= $this->endSection() ?>


<?= $this->section('content') ?>

<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Buat Laporan</h3>
            <p class="text-muted mb-0">
                Laporkan masalah kebersihan di lingkungan kampus.
            </p>
        </div>

        <a href="/pelapor/dashboard" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali
        </a>
    </div>

    <div class="form-card">

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <?php if (isset($validation)): ?>
            <div class="alert alert-danger">
                <?= $validation->listErrors() ?>
            </div>
        <?php endif; ?>

        <div id="pesanOffline" class="alert alert-info d-none">
            <i class="bi bi-wifi-off me-2"></i>
            Kamu sedang offline. Laporan akan disimpan sementara di perangkat.
        </div>

        <div id="pesanSinkronisasi" class="alert alert-success d-none"></div>

        <form action="/pelapor/laporan/simpan"
              method="post"
              enctype="multipart/form-data"
              id="formLaporan">

            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label fw-semibold">Judul Laporan</label>

                <input type="text"
                       name="judul"
                       class="form-control"
                       placeholder="Contoh: Sampah menumpuk di depan kelas"
                       value="<?= old('judul') ?>"
                       required
                       minlength="5"
                       maxlength="150">
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Kategori</label>

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
                <label class="form-label fw-semibold">Deskripsi</label>

                <textarea name="deskripsi"
                          class="form-control"
                          rows="4"
                          placeholder="Jelaskan kondisi dan lokasi masalah..."
                          required
                          minlength="10"><?= old('deskripsi') ?></textarea>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Foto Kondisi</label>

                <input type="file"
                       name="foto"
                       id="foto"
                       class="form-control"
                       accept="image/jpeg,image/png,image/webp"
                       capture="environment"
                       required>

                <small class="text-muted">
                    Gunakan foto JPG, PNG, atau WEBP dengan ukuran maksimal 2 MB.
                </small>
            </div>

            <hr>

            <h5 class="fw-bold mb-2">
                <i class="bi bi-geo-alt me-2"></i>
                Lokasi Laporan
            </h5>

            <p class="text-muted">
                Gunakan GPS atau pilih titik lokasi langsung pada peta.
            </p>

            <button type="button"
                    class="btn btn-success"
                    onclick="ambilLokasi()">

                <i class="bi bi-crosshair me-1"></i>
                Ambil Lokasi Saya
            </button>

            <div id="statusLokasi" class="small text-muted mt-2">
                Lokasi belum dipilih.
            </div>

            <div id="map"></div>

            <div class="location-info">

                <div class="mb-2">
                    <strong>Nama Lokasi:</strong>
                    <span id="namaLokasiTampil">Belum diketahui</span>
                </div>

                <div class="mb-2">
                    <strong>Latitude:</strong>
                    <span id="latitudeTampil">-</span>
                </div>

                <div>
                    <strong>Longitude:</strong>
                    <span id="longitudeTampil">-</span>
                </div>

            </div>

            <input type="hidden"
                   name="latitude"
                   id="latitude"
                   value="<?= old('latitude') ?>">

            <input type="hidden"
                   name="longitude"
                   id="longitude"
                   value="<?= old('longitude') ?>">

            <input type="hidden"
                   name="nama_lokasi"
                   id="nama_lokasi"
                   value="<?= old('nama_lokasi') ?>">

            <div class="d-flex justify-content-end gap-2 mt-4">

                <a href="/pelapor/dashboard" class="btn btn-outline-secondary">
                    Batal
                </a>

                <button type="submit" class="btn btn-success" id="tombolKirim">
                    <i class="bi bi-send me-1"></i>
                    Kirim Laporan
                </button>

            </div>

        </form>

    </div>

</div>

<?= $this->endSection() ?>


<?= $this->section('scripts') ?>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Memuat fungsi penyimpanan offline -->
<script src="/offline-storage.js"></script>

<!-- Memuat fungsi sinkronisasi otomatis -->
<script src="/offline-sync.js"></script>

<script>
    let marker;
    let koordinatPeta = [-7.8, 110.4];

    const map = L.map('map').setView(koordinatPeta, 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);


    function tampilkanMarker(lat, lng) {

        if (marker) {
            map.removeLayer(marker);
        }

        marker = L.marker([lat, lng], {
            draggable: true
        }).addTo(map);

        marker.on('dragend', function (event) {
            const posisi = event.target.getLatLng();

            aturLokasi(posisi.lat, posisi.lng);
        });

        map.setView([lat, lng], 17);

        aturLokasi(lat, lng);
    }


    async function aturLokasi(lat, lng) {

        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;

        document.getElementById('latitudeTampil').textContent = lat;
        document.getElementById('longitudeTampil').textContent = lng;

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

                tampilkanMarker(lat, lng);

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

        tampilkanMarker(
            event.latlng.lat,
            event.latlng.lng
        );

    });


    // Mengembalikan lokasi lama jika validasi server gagal
    const latitudeLama = document.getElementById('latitude').value;
    const longitudeLama = document.getElementById('longitude').value;
    const namaLokasiLama = document.getElementById('nama_lokasi').value;

    if (latitudeLama && longitudeLama) {

        tampilkanMarker(
            parseFloat(latitudeLama),
            parseFloat(longitudeLama)
        );

        if (namaLokasiLama) {
            document.getElementById('nama_lokasi').value = namaLokasiLama;
            document.getElementById('namaLokasiTampil').textContent = namaLokasiLama;
        }
    }


    // Informasi status koneksi
    function perbaruiStatusKoneksi() {

        const pesan = document.getElementById('pesanOffline');

        if (!navigator.onLine) {
            pesan.classList.remove('d-none');
        } else {
            pesan.classList.add('d-none');
        }
    }

    window.addEventListener('online', perbaruiStatusKoneksi);
    window.addEventListener('offline', perbaruiStatusKoneksi);

    perbaruiStatusKoneksi();


    // Menyimpan laporan ketika pengguna sedang offline
    document.getElementById('formLaporan').addEventListener('submit', async function (event) {

        if (navigator.onLine) {
            return;
        }

        event.preventDefault();

        const form = event.target;
        const tombol = document.getElementById('tombolKirim');

        const fileFoto = document.getElementById('foto').files[0];

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

        if (!latitude || !longitude) {
            alert('Silakan tentukan lokasi laporan terlebih dahulu.');
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
                nama_lokasi: document.getElementById('nama_lokasi').value
            };

            const id = await simpanLaporanOffline(dataLaporan);

            alert(
                'Laporan berhasil disimpan sementara di perangkat dengan nomor antrean ' +
                id +
                '. Laporan belum dikirim ke server. Laporan akan disinkronkan ketika internet tersedia dan halaman aplikasi dibuka.'
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