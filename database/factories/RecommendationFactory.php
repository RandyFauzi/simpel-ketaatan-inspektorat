<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Recommendation;
use App\Models\Finding;

class RecommendationFactory extends Factory
{
    protected $model = Recommendation::class;

    public function definition(): array
    {
        return [
            'finding_id' => Finding::factory(),
            'kode_rekomendasi' => 'R-' . $this->faker->unique()->numberBetween(1000, 9999),
            'uraian_rekomendasi' => 'Melakukan pengembalian kas atas kelebihan bayar pengerjaan fisik atau denda keterlambatan proyek.',
            'status' => $this->faker->randomElement(['belum_sesuai', 'proses', 'sesuai']),
        ];
    }
}
