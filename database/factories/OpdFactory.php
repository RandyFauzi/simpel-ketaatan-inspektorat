<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Opd;

class OpdFactory extends Factory
{
    protected $model = Opd::class;

    public function definition(): array
    {
        $dinas = ['Dinas Pekerjaan Umum', 'Dinas Pendidikan', 'Dinas Kesehatan', 'Dinas Perhubungan', 'Dinas Pendapatan Daerah', 'Dinas Pariwisata', 'Badan Kepegawaian Daerah', 'Dinas Lingkungan Hidup'];
        return [
            'kode_opd' => 'OPD-' . $this->faker->unique()->numberBetween(100, 999),
            'nama_opd' => $this->faker->unique()->randomElement($dinas),
            'nama_kepala' => $this->faker->name(),
        ];
    }
}
