<?php

namespace App\Http\Controllers;

use App\Models\Movement;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventarioController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()->with('category')->orderBy('name')->get();

        $selected = null;
        if ($request->filled('barcode')) {
            $selected = Product::where('barcode', trim((string) $request->query('barcode')))->first();
        } elseif ($request->filled('product_id')) {
            $selected = Product::find($request->query('product_id'));
        }

        return view('inventario', compact('products', 'selected'));
    }

    public function registerAdjust(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'adjustment' => ['required', 'integer'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $adjustment = (int) $data['adjustment'];
        $before = $product->stock;
        $newStock = $before + $adjustment;

        if ($newStock < 0) {
            return back()->withErrors([
                'adjustment' => 'Ajuste não permitido. Estoque atual: ' . $before . '. Não é possível reduzir mais que ' . $before . ' unidades.',
            ])->withInput();
        }

        $product->update(['stock' => $newStock]);

        if ($adjustment !== 0) {
            $lastCostCenter = Movement::where('product_id', $product->id)
                ->whereNotNull('cost_center_id')
                ->latest()
                ->value('cost_center_id');

            Movement::create([
                'product_id' => $product->id,
                'cost_center_id' => $lastCostCenter,
                'type' => Movement::TYPE_ADJUST,
                'quantity' => abs($adjustment),
                'stock_before' => $before,
                'stock_after' => $newStock,
                'user_id' => auth()->id(),
                'note' => $adjustment > 0 ? 'Sobra em inventário' : 'Quebra/Falta em inventário',
            ]);
        }

        return to_route('inventario', ['product_id' => $product->id])
            ->with('success', 'Ajuste registrado. Estoque: ' . $newStock . ' (' . ($adjustment > 0 ? '+' : '') . $adjustment . ')');
    }
}