// ==========================================
// SINKRONISASI LAPORAN OFFLINE
// ==========================================

async function sinkronkanLaporanOffline() {
    // Pastikan browser sedang terhubung ke internet.
    if (!navigator.onLine) {
        return;
    }

    // Pastikan halaman memiliki form laporan.
    const form = document.querySelector(
        'form[action*="/pelapor/laporan/simpan"]'
    );

    if (!form) {
        return;
    }

    // Ambil token CSRF dari input hidden pertama.
    const inputCsrf = form.querySelector(
        'input[type="hidden"][name]'
    );

    if (!inputCsrf) {
        console.error('Token keamanan CSRF tidak ditemukan.');
        return;
    }

    try {
        // Ambil seluruh laporan yang tersimpan di IndexedDB.
        const daftarLaporan = await ambilLaporanOffline();

        if (!daftarLaporan || daftarLaporan.length === 0) {
            return;
        }

        console.log(
            'Memulai sinkronisasi:',
            daftarLaporan.length,
            'laporan.'
        );

        for (const laporan of daftarLaporan) {
            // Jika koneksi terputus, hentikan proses.
            if (!navigator.onLine) {
                break;
            }

            try {
                const formData = new FormData();

                formData.append('judul', laporan.judul);
                formData.append('kategori', laporan.kategori);
                formData.append('deskripsi', laporan.deskripsi);
                formData.append('latitude', laporan.latitude);
                formData.append('longitude', laporan.longitude);
                formData.append(
                    'nama_lokasi',
                    laporan.nama_lokasi || ''
                );

                // Sertakan foto laporan.
                if (laporan.foto) {
                    formData.append(
                        'foto',
                        laporan.foto,
                        laporan.foto.name || 'foto-laporan.jpg'
                    );
                }

                // Sertakan token CSRF terbaru.
                formData.append(
                    inputCsrf.name,
                    inputCsrf.value
                );

                const respons = await fetch(
                    '/pelapor/laporan/sinkronisasi',
                    {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json'
                        }
                    }
                );

                const hasil = await respons.json();

                // Perbarui token CSRF jika server mengirim token baru.
                if (hasil.csrfHash) {
                    inputCsrf.value = hasil.csrfHash;
                }

                // Jika laporan berhasil disimpan di server.
                if (respons.ok && hasil.status === 'success') {

                    // Hapus laporan dari penyimpanan offline.
                    await hapusLaporanOffline(laporan.id);

                    console.log(
                        'Laporan berhasil disinkronkan:',
                        laporan.judul
                    );

                } else {
                    console.error(
                        'Sinkronisasi gagal:',
                        hasil.pesan || 'Terjadi kesalahan.'
                    );

                    // Hentikan proses agar laporan tetap tersimpan.
                    break;
                }

            } catch (error) {
                console.error(
                    'Gagal mengirim laporan:',
                    error
                );

                // Jangan hapus laporan jika pengiriman gagal.
                break;
            }
        }

        // Periksa apakah masih ada laporan yang menunggu.
        const sisaLaporan = await ambilLaporanOffline();

        if (sisaLaporan.length === 0) {
            console.log('Semua laporan offline berhasil disinkronkan.');
        } else {
            console.log(
                'Masih ada',
                sisaLaporan.length,
                'laporan yang menunggu sinkronisasi.'
            );
        }

    } catch (error) {
        console.error(
            'Gagal menjalankan sinkronisasi offline:',
            error
        );
    }
}


// ==========================================
// JALANKAN SAAT HALAMAN DIBUKA
// ==========================================

document.addEventListener('DOMContentLoaded', function () {
    if (navigator.onLine) {
        sinkronkanLaporanOffline();
    }
});


// ==========================================
// JALANKAN SAAT INTERNET KEMBALI TERHUBUNG
// ==========================================

window.addEventListener('online', function () {
    console.log('Internet kembali terhubung.');

    sinkronkanLaporanOffline();
});