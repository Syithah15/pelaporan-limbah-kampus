<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Periksa apakah pengguna sudah login
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        // Periksa role yang diperbolehkan
        $rolePengguna = session()->get('role');
        $roleDiizinkan = $arguments[0] ?? null;

        if ($rolePengguna !== $roleDiizinkan) {
            // Arahkan kembali ke dashboard sesuai role pengguna
            switch ($rolePengguna) {
                case 'pelapor':
                    return redirect()->to('/pelapor/dashboard');

                case 'petugas':
                    return redirect()->to('/petugas/dashboard');

                case 'admin':
                    return redirect()->to('/admin/dashboard');

                default:
                    session()->destroy();
                    return redirect()->to('/');
            }
        }
    }

    public function after(
        RequestInterface $request,
        ResponseInterface $response,
        $arguments = null
    ) {
        // Tidak ada proses tambahan setelah halaman dibuka
    }
}