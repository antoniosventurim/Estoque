<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));

        $query = Employee::query();

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        $employees = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('funcionarios.index', compact('employees', 'search'));
    }

    public function create(): View
    {
        return view('funcionarios.form', ['employee' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        Employee::create($this->validated($request));

        return redirect()->route('funcionarios.index')->with('status', 'Funcionário cadastrado.');
    }

    public function edit(Employee $employee): View
    {
        return view('funcionarios.form', compact('employee'));
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $employee->update($this->validated($request, $employee));

        return redirect()->route('funcionarios.index')->with('status', 'Funcionário atualizado.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();

        return redirect()->route('funcionarios.index')->with('status', 'Funcionário excluído.');
    }

    protected function validated(Request $request, ?Employee $employee = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:employees,name,'.optional($employee)->id],
        ]);
    }
}
