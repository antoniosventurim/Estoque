<?php

namespace App\Http\Controllers;

use App\Exceptions\DomainException;
use App\Http\Requests\UsuarioRequest;
use App\Models\User;
use App\Services\UsuarioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller
{
    public function __construct(private readonly UsuarioService $usuarios) {}

    public function index(): View
    {
        $users = User::with('roles')->orderBy('name')->get();

        return view('usuarios.index', compact('users'));
    }

    public function create(): View
    {
        return view('usuarios.form', [
            'user' => null,
            'roles' => Role::orderBy('name')->get(),
            'selectedRoles' => collect(),
        ]);
    }

    public function store(UsuarioRequest $request): RedirectResponse
    {
        $this->usuarios->create(
            $request->validated(),
            $request->input('roles', [])
        );

        return redirect()->route('usuarios')->with('status', 'Usuário cadastrado.');
    }

    public function edit(User $user): View
    {
        return view('usuarios.form', [
            'user' => $user,
            'roles' => Role::orderBy('name')->get(),
            'selectedRoles' => collect(old('roles', $user->roles->pluck('name'))),
        ]);
    }

    public function update(UsuarioRequest $request, User $user): RedirectResponse
    {
        $this->usuarios->update(
            $user,
            $request->validated(),
            $request->input('roles', [])
        );

        return redirect()->route('usuarios')->with('status', 'Usuário atualizado.');
    }

    public function destroy(User $user): RedirectResponse
    {
        try {
            $this->usuarios->destroy($user);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('usuarios')->with('status', 'Usuário excluído.');
    }
}
