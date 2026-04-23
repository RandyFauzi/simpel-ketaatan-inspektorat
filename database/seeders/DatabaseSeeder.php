<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Opd;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create OPDs (35 Official Institutions of Barito Selatan)
        $opdNames = [
            'Badan Kepegawaian dan Pengembangan Sumber Daya Manusia', 'Badan Kesatuan Bangsa dan Politik', 
            'Badan Penanggulangan Bencana Daerah', 'Badan Pendapatan Daerah', 'Badan Pengelolaan Keuangan dan Aset Daerah', 
            'Badan Perencanaan Pembangunan, Riset dan Inovasi Daerah', 'Dinas Pemuda dan Olahraga', 
            'Dinas Kependudukan dan Pencatatan Sipil', 'Dinas Kesehatan', 'Dinas Ketahanan Pangan dan Perikanan', 
            'Dinas Komunikasi dan Informatika', 'Dinas Koperasi, Usaha Kecil dan Menengah', 'Dinas Lingkungan Hidup', 
            'Dinas Pemadam Kebakaran', 'Dinas Pemberdayaan Masyarakat dan Desa', 
            'Dinas Pemberdayaan Perempuan, Perlindungan Anak, Pengendalian Penduduk dan Keluarga Berencana', 
            'Dinas Penanaman Modal dan Pelayanan Terpadu Satu Pintu', 'Dinas Pendidikan', 'Dinas Perhubungan', 
            'Dinas Perikanan', 'Dinas Perpustakaan dan Kearsipan', 'Dinas Perumahan Rakyat, Kawasan Permukiman dan Pertanahan', 
            'Dinas Pekerjaan Umum dan Penataan Ruang', 'Dinas Sosial', 'Dinas Tenaga Kerja dan Transmigrasi', 
            'Inspektorat Daerah', 'Kecamatan Dusun Hilir', 'Kecamatan Dusun Selatan', 'Kecamatan Dusun Utara', 
            'Kecamatan Gunung Bintang Awai', 'Kecamatan Jenamas', 'Kecamatan Karau Kuala', 'Satuan Polisi Pamong Praja', 
            'Sekretariat Daerah', 'Sekretariat Dewan Perwakilan Rakyat Daerah'
        ];

        $opds = collect();
        foreach ($opdNames as $index => $name) {
            $opds->push(Opd::firstOrCreate(['nama_opd' => $name], [
                'kode_opd' => 'OPD-' . str_pad((string)($index + 1), 3, '0', STR_PAD_LEFT),
                'nama_kepala' => 'Kepala ' . $name
            ]));
        }

        // 2. Create Admin Role
        User::firstOrCreate(['email' => 'admin@audit.local'], [
            'name' => 'System Admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 3. Create Inspektur Daerah
        User::firstOrCreate(['email' => 'inspektur@audit.local'], [
            'name' => 'YURISTIANTI YUDHA, S.Hut., M.M., CGCAE',
            'password' => Hash::make('password'),
            'role' => 'inspektur_daerah',
        ]);

        // 4. Create Inspektur Pembantu 1
        User::firstOrCreate(['email' => 'irban1@audit.local'], [
            'name' => 'GOZALI RAHMAN, S.Hut., M.AP., FRMP',
            'password' => Hash::make('password'),
            'role' => 'inspektur_pembantu_1',
        ]);

        // 5. Create Ketua Tim (Tim 1 & Tim 2)
        User::firstOrCreate(['email' => 'ketua.tim1@audit.local'], [
            'name' => 'SIGIT HERO CHRISTANTO, S.E.,M.M',
            'password' => Hash::make('password'),
            'role' => 'ketua_tim',
            'tim' => 'tim_1',
        ]);

        User::firstOrCreate(['email' => 'ketua.tim2@audit.local'], [
            'name' => 'MASWAN, S.Kom',
            'password' => Hash::make('password'),
            'role' => 'ketua_tim',
            'tim' => 'tim_2',
        ]);

        // 6. Create Auditor (Tim 1 & Tim 2)
        User::firstOrCreate(['email' => 'auditor.tim1@audit.local'], [
            'name' => 'Auditor Dummy Tim 1',
            'password' => Hash::make('password'),
            'role' => 'auditor',
            'tim' => 'tim_1',
        ]);

        User::firstOrCreate(['email' => 'auditor.tim2@audit.local'], [
            'name' => 'Auditor Dummy Tim 2',
            'password' => Hash::make('password'),
            'role' => 'auditor',
            'tim' => 'tim_2',
        ]);
        
        // No dummy LHP transactions are seeded per user instructions.
    }
}
