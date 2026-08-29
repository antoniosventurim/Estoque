<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Unit::create($data);

        return redirect()->route('unidades.index')->with('status', 'Unidade cadastrada.');
    }

    public function edit(Unit $unit): View
    {
        return view('unidades.form', compact('unit'));
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $data = $this->validated($request, $unit);

        $unit->update($data);

        return redirect()->route('unidades.index')->with('status', 'Unidade atualizada.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $unit->delete();

        return redirect()->route('unidades.index')->with('status', 'Unidade excluída.');
    }

    protected function validated(Request $request, ?Unit $unit = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'abbreviation' => ['required', 'string', 'max:10', 'unique:units,abbreviation,'.optional($unit)->id],
        ]);
    }
}
