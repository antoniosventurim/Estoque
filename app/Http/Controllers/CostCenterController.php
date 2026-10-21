<?php

namespace App\Http\Controllers;

use App\Http\Requests\CostCenterRequest;
use App\Models\CostCenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CostCenterController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $type = (string) $request->query('type', '');

        $query = CostCenter::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($type !== '') {
            $query->where('type', $type);
        }

        $costCenters = $query->withCount('movements')->paginate(10)->withQueryString();

        return view('centros-custo.index', compact('costCenters', 'search', 'type'));
    }

    public function create(): View
    {
        return view('centros-custo.form', ['costCenter' => null]);
    }

    public function store(CostCenterRequest $request): RedirectResponse
    {
        CostCenter::create($request->validated());

        return redirect()->route('centros-custo.index')->with('status', 'Centro de custo cadastrado.');
    }

    public function edit(CostCenter $costCenter): View
    {
        return view('centros-custo.form', compact('costCenter'));
    }

    public function update(CostCenterRequest $request, CostCenter $costCenter): RedirectResponse
    {
        $costCenter->update($request->validated());

        return redirect()->route('centros-custo.index')->with('status', 'Centro de custo atualizado.');
    }

    public function destroy(CostCenter $costCenter): RedirectResponse
    {
        $costCenter->delete();

        return redirect()->route('centros-custo.index')->with('status', 'Centro de custo excluído.');
    }
}
