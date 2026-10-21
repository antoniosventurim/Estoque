<?php

namespace App\Services;

use App\Exceptions\DomainException;
use Spatie\Permission\Models\Role;

class RoleService
{
    /**
     * @param  array<int, string>  $permissions
     */
    public function create(string $name, array $permissions = []): Role
    {
        $role = Role::create(['name' => $name]);
        $role->syncPermissions($permissions);

        return $role;
    }

    /**
     * @param  array<int, string>  $permissions
     */
    public function update(Role $role, string $name, array $permissions = []): Role
    {
        $role->update(['name' => $name]);
        $role->syncPermissions($permissions);

        return $role->refresh();
    }

    public function destroy(Role $role): void
    {
        if ($role->name === 'Administrador') {
            throw new DomainException('O perfil Administrador não pode ser excluído.');
        }

        $role->delete();
    }
}
