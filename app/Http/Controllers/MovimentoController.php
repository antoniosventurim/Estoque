<?php

namespace App\Http\Controllers;

use App\Models\CostCenter;
use App\Models\Employee;
use App\Models\Movement;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MovimentoController extends Controller
{
    public function saida(Request $request): View
    {
        [$product, $notFound] = $this->lookupProduct($request);
        $costCenters = $this->activeCostCenters();
        $employees = Employee::orderBy('name')->get();
        $products = $this->productOptions();

        return view('saida', compact('product', 'notFound', 'costCenters', 'employees', 'products'));
    }

    private function activeCostCenters()
    {
        return CostCenter::where('is_active', true)->orderBy('type')->orderBy('name')->get();
    }

    public function saidaRegister(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'barcode' => ['required', 'string'],
            'quantity' => ['required', 'integer', 'min:1'],
            'cost_center_id' => ['nullable', 'exists:cost_centers,id'],
            'employee_id' => ['required', 'exists:employees,id'],
        ]);

        $product = Product::where('barcode', $data['barcode'])->first();

        if (! $product) {
            return back()->with('error', 'Produto não encontrado.');
        }

        $quantity = (int) $data['quantity'];
        $before = $product->stock;

        if ($quantity > $before) {
            return back()->with('error', 'Quantidade solicitada maior que o estoque disponível.');
        }

        $employee = Employee::findOrFail((int) $data['employee_id']);
        $product->update(['stock' => $before - $quantity]);

        Movement::create([
            'product_id' => $product->id,
            'type' => Movement::TYPE_OUT,
            'quantity' => $quantity,
            'stock_before' => $before,
            'stock_after' => $product->stock,
            'user_id' => auth()->id(),
            'employee_id' => $employee->id,
            'cost_center_id' => $this->costCenterId($data),
        ]);

        return redirect()->route('saida')->with('success', [
            'name' => $product->name,
            'qty' => $quantity,
            'before' => $before,
            'after' => $product->stock,
            'min_stock' => $product->min_stock,
            'employee' => $employee->name,
        ]);
    }

    public function entrada(Request $request): View
    {
        [$product, $notFound] = $this->lookupProduct($request);
        $costCenters = $this->activeCostCenters();
        $products = $this->productOptions();

        return view('entrada', compact('product', 'notFound', 'costCenters', 'products'));
    }

    public function entradaRegister(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'barcode' => ['required', 'string'],
            'quantity' => ['required', 'integer', 'min:1'],
            'cost_center_id' => ['nullable', 'exists:cost_centers,id'],
        ]);

        $product = Product::where('barcode', $data['barcode'])->first();

        if (! $product) {
            return back()->with('error', 'Produto não encontrado.');
        }

        $quantity = (int) $data['quantity'];
        $before = $product->stock;
        $product->update(['stock' => $before + $quantity]);

        Movement::create([
            'product_id' => $product->id,
            'type' => Movement::TYPE_IN,
            'quantity' => $quantity,
            'stock_before' => $before,
            'stock_after' => $product->stock,
            'user_id' => auth()->id(),
            'cost_center_id' => $this->costCenterId($data),
        ]);

        return redirect()->route('entrada')->with('success', [
            'name' => $product->name,
            'qty' => $quantity,
            'before' => $before,
            'after' => $product->stock,
            'min_stock' => $product->min_stock,
            'user' => auth()->user()->name,
        ]);
    }

    private function lookupProduct(Request $request): array
    {
        $barcode = trim((string) $request->query('barcode'));

        if ($barcode === '') {
            return [null, false];
        }

        $product = Product::where('barcode', $barcode)->first();

        return [$product, $product === null];
    }

    private function productOptions(): array
    {
        return Product::orderBy('name')
            ->get()
            ->map(fn (Product $p) => [
                'id' => $p->barcode,
                'name' => $p->name,
                'sub' => 'Cod: '.$p->barcode,
            ])
            ->values()
            ->all();
    }

    private function costCenterId(array $data): ?int
    {
        $id = $data['cost_center_id'] ?? null;

        return $id !== null && $id !== '' ? (int) $id : null;
    }
}