<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeRequest;
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

    public function store(EmployeeRequest $request): RedirectResponse
    {
        Employee::create($request->validated());

        return redirect()->route('funcionarios.index')->with('status', 'Funcionário cadastrado.');
    }

    public function edit(Employee $employee): View
    {
        return view('funcionarios.form', compact('employee'));
    }

    public function update(EmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $employee->update($request->validated());

        return redirect()->route('funcionarios.index')->with('status', 'Funcionário atualizado.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();

        return redirect()->route('funcionarios.index')->with('status', 'Funcionário excluído.');
    }
}
