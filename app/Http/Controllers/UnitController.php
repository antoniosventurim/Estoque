<?php

namespace App\Http\Controllers;

use App\Http\Requests\UnitRequest;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(): View
    {
        $units = Unit::withCount('products')->orderBy('name')->get();

        return view('unidades.index', compact('units'));
    }

    public function create(): View
    {
        return view('unidades.form', ['unit' => null]);
    }

    public function store(UnitRequest $request): RedirectResponse
    {
        Unit::create($request->validated());

        return redirect()->route('unidades.index')->with('status', 'Unidade cadastrada.');
    }

    public function edit(Unit $unit): View
    {
        return view('unidades.form', compact('unit'));
    }

    public function update(UnitRequest $request, Unit $unit): RedirectResponse
    {
        $unit->update($request->validated());

        return redirect()->route('unidades.index')->with('status', 'Unidade atualizada.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $unit->delete();

        return redirect()->route('unidades.index')->with('status', 'Unidade excluída.');
    }
}
