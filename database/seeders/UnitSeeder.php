<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Unidade', 'abbreviation' => 'un'],
            ['name' => 'Quilograma', 'abbreviation' => 'kg'],
            ['name' => 'Grama', 'abbreviation' => 'g'],
            ['name' => 'Litro', 'abbreviation' => 'l'],
            ['name' => 'Mililitro', 'abbreviation' => 'ml'],
            ['name' => 'Metro', 'abbreviation' => 'm'],
            ['name' => 'Caixa', 'abbreviation' => 'cx'],
            ['name' => 'Pacote', 'abbreviation' => 'pct'],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(
                ['abbreviation' => $unit['abbreviation']],
                $unit
            );
        }
    }
}
