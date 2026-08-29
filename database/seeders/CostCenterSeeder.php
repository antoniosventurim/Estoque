<?php

namespace Database\Seeders;

use App\Models\CostCenter;
use Illuminate\Database\Seeder;

class CostCenterSeeder extends Seeder
{
    public function run(): void
    {
        $centers = [
            // Cursos
            ['name' => 'Medicina', 'type' => 'curso', 'code' => 'MED', 'color' => '#c8102e'],
            ['name' => 'Enfermagem', 'type' => 'curso', 'code' => 'ENF', 'color' => '#0f9d58'],
            ['name' => 'Odontologia', 'type' => 'curso', 'code' => 'ODO', 'color' => '#1a73e8'],
            ['name' => 'Fisioterapia', 'type' => 'curso', 'code' => 'FIS', 'color' => '#e37400'],
            ['name' => 'Biomedicina', 'type' => 'curso', 'code' => 'BIO', 'color' => '#7b2ff7'],
            ['name' => 'Farmácia', 'type' => 'curso', 'code' => 'FAR', 'color' => '#00897b'],
            ['name' => 'Nutrição', 'type' => 'curso', 'code' => 'NUT', 'color' => '#d81b60'],
            ['name' => 'Administração', 'type' => 'curso', 'code' => 'ADM', 'color' => '#3949ab'],
            // Setores / laboratórios / administrativo
            ['name' => 'Laboratório de Anatomia', 'type' => 'laboratorio', 'code' => 'LAB-ANA', 'color' => '#6d4c41'],
            ['name' => 'Laboratório de Enfermagem', 'type' => 'laboratorio', 'code' => 'LAB-ENF', 'color' => '#4db6ac'],
            ['name' => 'Clínica de Odontologia', 'type' => 'setor', 'code' => 'CLI-ODO', 'color' => '#fbc02d'],
            ['name' => 'Biblioteca', 'type' => 'setor', 'code' => 'BIB', 'color' => '#5e35b1'],
            ['name' => 'Coordenação Acadêmica', 'type' => 'administrativo', 'code' => 'CO-ACA', 'color' => '#546e7a'],
            ['name' => 'Financeiro', 'type' => 'administrativo', 'code' => 'FIN', 'color' => '#607d8b'],
            ['name' => 'TI / Infraestrutura', 'type' => 'setor', 'code' => 'TI', 'color' => '#0288d1'],
        ];

        foreach ($centers as $center) {
            CostCenter::firstOrCreate(['name' => $center['name']], $center);
        }
    }
}