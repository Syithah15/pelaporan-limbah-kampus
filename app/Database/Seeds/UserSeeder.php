<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'nama'       => 'Mahasiswa Penguji',
                'email'      => 'pelapor@test.com',
                'nim'        => '20260001',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role'       => 'pelapor',
                'is_active'  => 1,
            ],
            [
                'nama'       => 'Petugas Kebersihan',
                'email'      => 'petugas@test.com',
                'nim'        => '20260002',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role'       => 'petugas',
                'is_active'  => 1,
            ],
            [
                'nama'       => 'Administrator',
                'email'      => 'admin@test.com',
                'nim'        => '20260003',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role'       => 'admin',
                'is_active'  => 1,
            ],
        ];

        foreach ($users as $user) {
            $existingUser = $this->db->table('users')
                ->where('email', $user['email'])
                ->get()
                ->getRowArray();

            if (!$existingUser) {
                $this->db->table('users')->insert($user);
            }
        }
    }
}