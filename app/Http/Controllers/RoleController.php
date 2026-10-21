<?php

namespace App\Http\Controllers;

use App\Exceptions\DomainException;
use App\Http\Requests\RoleRequest;
use App\Services\RoleService;
use App\Support\PermissionMap;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(private readonly RoleService $roles) {}

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

    public function store(RoleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->roles->create($data['name'], $data['permissions'] ?? []);

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

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        $data = $request->validated();

        $this->roles->update($role, $data['name'], $data['permissions'] ?? []);

        return redirect()->route('roles.index')->with('status', 'Perfil atualizado.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        try {
            $this->roles->destroy($role);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('roles.index')->with('status', 'Perfil excluído.');
    }
}
