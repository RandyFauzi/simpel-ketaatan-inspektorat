<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Finding;
use App\Models\Lhp;

class FindingFactory extends Factory
{
    protected $model = Finding::class;

    public function definition(): array
    {
        return [
            'lhp_id' => Lhp::factory(),
            'kode_temuan' => 'T-' . $this->faker->unique()->numberBetween(1000, 9999),
            'uraian_temuan' => $this->faker->paragraph(),
            'kerugian_negara' => $this->faker->randomFloat(2, 1000000, 50000000),
            'kerugian_daerah' => $this->faker->randomFloat(2, 0, 10000000),
        ];
    }
}
