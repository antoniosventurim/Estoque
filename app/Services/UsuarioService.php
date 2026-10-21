<?php

namespace App\Services;

use App\Exceptions\DomainException;
use App\Models\User;

class UsuarioService
{
    /**
     * @param  array<string, mixed>  $data  dados validados
     * @param  array<int, string>  $roles  nomes dos perfis
     */
    public function create(array $data, array $roles = []): User
    {
        $user = User::create($data);
        $user->syncRoles($roles);

        return $user;
    }

    /**
     * @param  array<string, mixed>  $data  dados validados
     * @param  array<int, string>  $roles  nomes dos perfis
     */
    public function update(User $user, array $data, array $roles = []): User
    {
        $user->update($data);
        $user->syncRoles($roles);

        return $user->refresh();
    }

    public function destroy(User $user): void
    {
        if ($user->id === auth()->id()) {
            throw new DomainException('Você não pode excluir o próprio usuário.');
        }

        $user->delete();
    }
}
