<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>

<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
/>

<style>
    #petaLaporan {
        width: 100%;
        height: 550px;
        border-radius: 12px;
        z-index: 1;
    }

    .info-peta {
        font-size: 14px;
        line-height: 1.7;
    }
</style>

<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid">

    <!-- JUDUL -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3 class="fw-bold mb-1">Peta Sebaran Laporan</h3>

            <p class="text-muted mb-0">
                Pantau persebaran laporan kebersihan di lingkungan kampus.
            </p>
        </div>

        <a href="<?= site_url('/admin/laporan') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
            Rekap Laporan
        </a>

    </div>

    <!-- INFORMASI JUMLAH LAPORAN -->
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <div class="d-flex align-items-center gap-3">

                <div class="bg-success bg-opacity-10 text-success rounded p-3">
                    <i class="bi bi-geo-alt fs-3"></i>
                </div>

                <div>
                    <p class="text-muted mb-1">
                        Total laporan dengan koordinat lokasi
                    </p>

                    <h3 class="fw-bold mb-0">
                        <?= count($laporanPeta) ?>
                    </h3>
                </div>

            </div>

        </div>
    </div>

    <!-- PETA -->
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-success text-white py-3">
            <h5 class="mb-0">
                <i class="bi bi-map"></i>
                Peta Lokasi Laporan
            </h5>
        </div>

        <div class="card-body">

            <?php if (!empty($laporanPeta)): ?>

                <div id="petaLaporan"></div>

            <?php else: ?>

                <div class="text-center py-5">

                    <i class="bi bi-geo-alt text-muted" style="font-size: 50px;"></i>

                    <h5 class="mt-3">Belum Ada Lokasi Laporan</h5>

                    <p class="text-muted mb-0">
                        Belum ada laporan yang memiliki koordinat lokasi.
                    </p>

                </div>

            <?php endif; ?>

        </div>
    </div>

    <!-- KETERANGAN STATUS -->
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">Keterangan Status Laporan</h5>
        </div>

        <div class="card-body">

            <div class="d-flex flex-wrap gap-4">

                <div>
                    <span class="badge bg-warning text-dark">MENUNGGU</span>
                    <small class="text-muted ms-1">Belum ditangani</small>
                </div>

                <div>
                    <span class="badge bg-primary">DIPROSES</span>
                    <small class="text-muted ms-1">Sedang dikerjakan</small>
                </div>

                <div>
                    <span class="badge bg-success">SELESAI</span>
                    <small class="text-muted ms-1">Sudah diselesaikan</small>
                </div>

                <div>
                    <span class="badge bg-danger">DIBATALKAN</span>
                    <small class="text-muted ms-1">Dibatalkan Admin</small>
                </div>

            </div>

        </div>
    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

<?php if (!empty($laporanPeta)): ?>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    const dataLaporan = <?= json_encode(
        $laporanPeta,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
    ) ?>;

    // Membuat peta dengan titik awal Indonesia.
    const peta = L.map('petaLaporan').setView([-7.8, 110.4], 5);

    // Menggunakan peta OpenStreetMap.
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(peta);

    const semuaMarker = [];

    // Warna marker berdasarkan status laporan.
    function warnaStatus(status) {
        const warna = {
            'MENUNGGU': '#ffc107',
            'DIPROSES': '#0d6efd',
            'SELESAI': '#198754',
            'DIBATALKAN': '#dc3545'
        };

        return warna[status] || '#6c757d';
    }

    // Menambahkan marker setiap laporan.
    dataLaporan.forEach(function(item) {

        const latitude = Number(item.latitude);
        const longitude = Number(item.longitude);

        if (
            !Number.isFinite(latitude) ||
            !Number.isFinite(longitude) ||
            latitude < -90 ||
            latitude > 90 ||
            longitude < -180 ||
            longitude > 180
        ) {
            return;
        }

        const marker = L.circleMarker([latitude, longitude], {
            radius: 9,
            fillColor: warnaStatus(item.status),
            color: '#ffffff',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.9
        }).addTo(peta);

        // Informasi ketika marker diklik.
        const isiPopup = `
            <div class="info-peta">
                <strong>${escapeHtml(item.judul)}</strong>
                <hr class="my-2">

                <strong>Pelapor:</strong><br>
                ${escapeHtml(item.nama_pelapor)}<br>

                <strong>Kategori:</strong><br>
                ${escapeHtml(item.kategori)}<br>

                <strong>Lokasi:</strong><br>
                ${escapeHtml(item.nama_lokasi || 'Tidak tersedia')}<br>

                <strong>Status:</strong><br>
                ${escapeHtml(item.status)}<br><br>

                <a href="<?= site_url('/admin/laporan/detail/') ?>${encodeURIComponent(item.id)}"
                   class="btn btn-sm btn-success">
                    Lihat Detail
                </a>
            </div>
        `;

        marker.bindPopup(isiPopup);

        semuaMarker.push(marker);
    });

    // Mengatur posisi peta berdasarkan semua marker.
    if (semuaMarker.length > 0) {

        const kelompokMarker = L.featureGroup(semuaMarker);

        peta.fitBounds(kelompokMarker.getBounds(), {
            padding: [40, 40],
            maxZoom: 16
        });

    }

    // Mengamankan teks yang dimasukkan ke popup.
    function escapeHtml(teks) {
        return String(teks ?? '').replace(/[&<>"']/g, function(karakter) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[karakter];
        });
    }
</script>

<?php endif; ?>

<?= $this->endSection() ?>