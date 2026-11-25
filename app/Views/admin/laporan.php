<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid">

    <!-- JUDUL HALAMAN -->
    <div class="mb-4">
        <h3 class="fw-bold mb-1">Rekap Laporan</h3>
        <p class="text-muted mb-0">
            Kelola dan pantau seluruh laporan kebersihan kampus.
        </p>
    </div>

    <!-- PESAN NOTIFIKASI -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <!-- STATISTIK LAPORAN -->
    <div class="row g-3 mb-4">

        <div class="col-md-4 col-lg">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Laporan</p>
                    <h3 class="fw-bold mb-0">
                        <?= esc($statistik['total']) ?>
                    </h3>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-lg">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Menunggu</p>
                    <h3 class="fw-bold text-warning mb-0">
                        <?= esc($statistik['menunggu']) ?>
                    </h3>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-lg">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Diproses</p>
                    <h3 class="fw-bold text-primary mb-0">
                        <?= esc($statistik['diproses']) ?>
                    </h3>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-lg">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Selesai</p>
                    <h3 class="fw-bold text-success mb-0">
                        <?= esc($statistik['selesai']) ?>
                    </h3>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-lg">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Dibatalkan</p>
                    <h3 class="fw-bold text-danger mb-0">
                        <?= esc($statistik['dibatalkan']) ?>
                    </h3>
                </div>
            </div>
        </div>

    </div>

    <!-- TABEL LAPORAN -->
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-success text-white py-3">
            <h5 class="mb-0">
                <i class="bi bi-clipboard-data"></i>
                Daftar Seluruh Laporan
            </h5>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>No.</th>
                            <th>Judul Laporan</th>
                            <th>Pelapor</th>
                            <th>Kategori</th>
                            <th>Lokasi</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (!empty($laporan)): ?>

                            <?php $no = 1; ?>

                            <?php foreach ($laporan as $item): ?>

                                <?php
                                $warnaStatus = [
                                    'MENUNGGU' => 'warning',
                                    'DIPROSES' => 'primary',
                                    'SELESAI' => 'success',
                                    'DIBATALKAN' => 'danger',
                                ];

                                $warna = $warnaStatus[$item['status']] ?? 'secondary';
                                ?>

                                <tr>

                                    <td><?= $no++ ?></td>

                                    <td class="fw-semibold">
                                        <?= esc($item['judul']) ?>
                                    </td>

                                    <td>
                                        <?= esc($item['nama_pelapor']) ?>
                                    </td>

                                    <td>
                                        <?= esc($item['kategori']) ?>
                                    </td>

                                    <td>
                                        <?= esc($item['nama_lokasi'] ?? 'Belum tersedia') ?>
                                    </td>

                                    <td>
                                        <?= esc($item['created_at']) ?>
                                    </td>

                                    <td>
                                        <span class="badge bg-<?= $warna ?>">
                                            <?= esc($item['status']) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <a
                                            href="<?= site_url('admin/laporan/detail/' . $item['id']) ?>"
                                            class="btn btn-sm btn-success"
                                        >
                                            <i class="bi bi-eye"></i>
                                            Detail
                                        </a>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    Belum ada laporan yang masuk.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>
    </div>

</div>

<?= $this->endSection() ?>