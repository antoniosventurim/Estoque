<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function index(): View
    {
        $users = User::orderBy('name')->get();

        return view('usuarios.index', compact('users'));
    }

    public function create(): View
    {
        return view('usuarios.form', ['user' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        User::create($this->validated($request));

        return redirect()->route('usuarios')->with('status', 'Usuário cadastrado.');
    }

    public function edit(User $user): View
    {
        return view('usuarios.form', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $user->update($this->validated($request, false));

        return redirect()->route('usuarios')->with('status', 'Usuário atualizado.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Você não pode excluir o próprio usuário.');
        }

        $user->delete();

        return redirect()->route('usuarios')->with('status', 'Usuário excluído.');
    }

    protected function validated(Request $request, bool $creating = true): array
    {
        $passwordRules = $creating
            ? ['required', 'confirmed', 'min:8']
            : ['nullable', 'confirmed', 'min:8'];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$request->route('user')?->id],
            'password' => $passwordRules,
            'is_admin' => ['nullable', 'boolean'],
        ]);

        $data['is_admin'] = $request->boolean('is_admin');

        if (! $creating && empty($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }
}
