<?php

namespace App\Controllers;

use App\Models\LaporanModel;
use App\Models\LokasiModel;
use App\Models\RiwayatStatusModel;

class LaporanController extends BaseController
{
    protected $laporanModel;
    protected $lokasiModel;
    protected $riwayatModel;

    public function __construct()
    {
        $this->laporanModel = new LaporanModel();
        $this->lokasiModel = new LokasiModel();
        $this->riwayatModel = new RiwayatStatusModel();
    }

    public function create()
    {
        return view('pelapor/buat_laporan');
    }

    public function store()
    {
        // Validasi data laporan
        $rules = [
            'judul' => 'required|min_length[5]|max_length[150]',
            'kategori' => 'required|in_list[Sampah,Saluran Air,Toilet,Ruang Kelas,Lingkungan,Lainnya]',
            'deskripsi' => 'required|min_length[10]',
            'foto' => 'uploaded[foto]|is_image[foto]|max_size[foto,2048]',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Mengambil koordinat
        $latitude = (float) $this->request->getPost('latitude');
        $longitude = (float) $this->request->getPost('longitude');

        // Memastikan koordinat berada dalam rentang yang valid
        if (
            $latitude < -90 || $latitude > 90 ||
            $longitude < -180 || $longitude > 180
        ) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Koordinat lokasi tidak valid.');
        }

        // Mengambil nama lokasi
        $namaLokasi = trim(
            strip_tags((string) $this->request->getPost('nama_lokasi'))
        );

        // Membatasi nama lokasi maksimal 150 karakter
        if ($namaLokasi !== '') {
            $namaLokasi = mb_substr($namaLokasi, 0, 150);
        } else {
            $namaLokasi = null;
        }

        // Mengambil file foto
        $foto = $this->request->getFile('foto');

        if (!$foto || !$foto->isValid()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Foto tidak valid atau gagal diunggah.');
        }

        // Menyiapkan folder penyimpanan foto
        $uploadPath = WRITEPATH . 'uploads/laporan';

        if (!is_dir($uploadPath)) {
            if (!mkdir($uploadPath, 0775, true) && !is_dir($uploadPath)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Folder penyimpanan foto tidak dapat dibuat.');
            }
        }

        // Membuat nama foto secara acak
        $namaFoto = $foto->getRandomName();

        try {
            $foto->move($uploadPath, $namaFoto);
        } catch (\Throwable $e) {
            log_message('error', $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Foto gagal disimpan.');
        }

        // Memulai transaksi database
        $db = \Config\Database::connect();

        $db->transBegin();

        try {

            // Menyimpan data laporan
            $laporanId = $this->laporanModel->insert([
                'user_id' => session()->get('user_id'),
                'judul' => trim($this->request->getPost('judul')),
                'kategori' => $this->request->getPost('kategori'),
                'deskripsi' => trim($this->request->getPost('deskripsi')),
                'foto' => $namaFoto,
                'status' => 'MENUNGGU',
            ]);

            if (!$laporanId) {
                throw new \RuntimeException(
                    'Gagal menyimpan data laporan.'
                );
            }

            // Menyimpan koordinat dan nama lokasi
            $lokasiBerhasil = $this->lokasiModel->insert([
                'laporan_id' => $laporanId,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'nama_lokasi' => $namaLokasi,
            ]);

            if (!$lokasiBerhasil) {
                throw new \RuntimeException(
                    'Gagal menyimpan lokasi laporan.'
                );
            }

            // Menyimpan riwayat status awal
            $riwayatBerhasil = $this->riwayatModel->insert([
                'laporan_id' => $laporanId,
                'user_id' => session()->get('user_id'),
                'status_sebelumnya' => null,
                'status_baru' => 'MENUNGGU',
                'keterangan' => 'Laporan berhasil dibuat.',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            if (!$riwayatBerhasil) {
                throw new \RuntimeException(
                    'Gagal menyimpan riwayat laporan.'
                );
            }

            // Memastikan transaksi berhasil
            if ($db->transStatus() === false) {
                throw new \RuntimeException(
                    'Transaksi database gagal.'
                );
            }

            $db->transCommit();

        } catch (\Throwable $e) {

            // Membatalkan transaksi jika terjadi kesalahan
            $db->transRollback();

            // Menghapus foto jika penyimpanan database gagal
            $pathFoto = $uploadPath . DIRECTORY_SEPARATOR . $namaFoto;

            if (is_file($pathFoto)) {
                unlink($pathFoto);
            }

            log_message('error', $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Laporan gagal disimpan. Silakan coba kembali.');
        }

        // Jika berhasil
        return redirect()->to('/pelapor/dashboard')
            ->with('success', 'Laporan berhasil dikirim!');
    }
}