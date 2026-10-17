<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\PermissionMap;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        PermissionMap::syncPermissions();

        $admin = Role::firstOrCreate(['name' => 'Administrador']);
        $admin->syncPermissions(array_keys(PermissionMap::all()));

        $operador = Role::firstOrCreate(['name' => 'Operador']);
        $operador->syncPermissions(PermissionMap::OPERATOR_PERMISSIONS);

        User::where('is_admin', true)->get()->each(function (User $user) use ($admin) {
            if (! $user->hasRole('Administrador')) {
                $user->assignRole($admin);
            }
        });
    }
}
