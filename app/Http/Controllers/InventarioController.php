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
            'found_qty' => ['required', 'integer', 'min:0'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $found = (int) $data['found_qty'];
        $difference = $found - $product->stock;
        $before = $product->stock;

        $product->update(['stock' => $found]);

        if ($difference !== 0) {
            Movement::create([
                'product_id' => $product->id,
                'type' => Movement::TYPE_ADJUST,
                'quantity' => abs($difference),
                'stock_before' => $before,
                'stock_after' => $found,
                'user_id' => auth()->id(),
                'note' => $difference > 0 ? 'Sobra em inventário' : 'Quebra/Falta em inventário',
            ]);
        }

        return to_route('inventario', ['product_id' => $product->id])
            ->with('success', 'Ajuste registrado.');
    }
}