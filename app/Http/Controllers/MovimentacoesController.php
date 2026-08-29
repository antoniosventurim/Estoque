<?php

namespace App\Http\Controllers;

use App\Models\CostCenter;
use App\Models\Employee;
use App\Models\Movement;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MovimentacoesController extends Controller
{
    public function index(Request $request): View
    {
        $type = (string) $request->query('type', '');
        $employee = (string) $request->query('employee', '');
        $costCenter = (string) $request->query('cost_center', '');
        $search = (string) $request->query('q', '');

        $query = Movement::query()->with('product', 'employee', 'costCenter');

        if ($type === 'in' || $type === 'out' || $type === 'adjust') {
            $query->where('type', $type);
        }

        if ($employee !== '') {
            $query->where('employee_id', $employee);
        }

        if ($costCenter !== '') {
            $query->where('cost_center_id', $costCenter);
        }

        if ($search !== '') {
            $query->whereHas('product', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $movements = $query->latest()->paginate(12)->withQueryString();

        $employees = Employee::orderBy('name')->get();
        $costCenters = CostCenter::orderBy('name')->get();

        return view('movimentacoes', compact('movements', 'employees', 'costCenters', 'type', 'employee', 'costCenter', 'search'));
    }
}
