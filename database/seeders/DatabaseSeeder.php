<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $this->call([
            UnitSeeder::class,
            CostCenterSeeder::class,
            EmployeeSeeder::class,
            ProdutosDemoSeeder::class,
        ]);
    }
}
