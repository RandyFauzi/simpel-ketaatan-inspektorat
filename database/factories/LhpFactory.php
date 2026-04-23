<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Lhp;
use App\Models\Opd;

class LhpFactory extends Factory
{
    protected $model = Lhp::class;

    public function definition(): array
    {
        $topics = ['Pembangunan Jembatan', 'Renovasi Gedung Sekolah', 'Pengadaan Alat Kesehatan', 'Proyek Pengaspalan Jalan', 'Belanja Bantuan Sosial', 'Pengadaan Kendaraan Dinas', 'Bansos Covid', 'Perizinan Tower'];
        return [
            'nomor_lhp' => 'LHP/' . $this->faker->unique()->numerify('###') . '/' . date('Y'),
            'tgl_lhp' => $this->faker->date(),
            'judul' => 'Pemeriksaan ' . $this->faker->randomElement($topics),
            'tahun_anggaran' => date('Y') - rand(0, 2),
            'opd_id' => Opd::factory(),
            'status' => $this->faker->randomElement(['draft', 'published']),
        ];
    }
}
