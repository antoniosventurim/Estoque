<?php

namespace App\Http\Controllers;

use App\Support\PermissionMap;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::withCount('permissions')->orderBy('name')->get();
        $builtinRole = 'Administrador';

        return view('roles.index', compact('roles', 'builtinRole'));
    }

    public function create(): View
    {
        return view('roles.form', [
            'role' => null,
            'modules' => PermissionMap::MODULES,
            'actionLabels' => PermissionMap::ACTION_LABELS,
            'selected' => collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $role = Role::create(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('roles.index')->with('status', 'Perfil criado com sucesso.');
    }

    public function edit(Role $role): View
    {
        return view('roles.form', [
            'role' => $role,
            'modules' => PermissionMap::MODULES,
            'actionLabels' => PermissionMap::ACTION_LABELS,
            'selected' => collect(old('permissions', $role->permissions->pluck('name'))),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $this->validated($request, $role);

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('roles.index')->with('status', 'Perfil atualizado.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->name === 'Administrador') {
            return back()->with('error', 'O perfil Administrador não pode ser excluído.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('status', 'Perfil excluído.');
    }

    protected function validated(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.optional($role)->id],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);
    }
}
