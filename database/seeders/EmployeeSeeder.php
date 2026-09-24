<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $employees = [
            'Maria Silva',
            'João Santos',
            'Ana Oliveira',
            'Pedro Costa',
            'Lucia Ferreira',
            'Carlos Souza',
            'Fernanda Lima',
            'Roberto Almeida',
        ];

        foreach ($employees as $name) {
            Employee::updateOrCreate(['name' => $name]);
        }
    }
}
