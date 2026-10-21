<?php

namespace App\Http\Controllers;

use App\Exceptions\DomainException;
use App\Http\Requests\InventarioAdjustRequest;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventarioController extends Controller
{
    public function __construct(private readonly StockService $stock) {}

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

    public function registerAdjust(InventarioAdjustRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $product = Product::findOrFail($data['product_id']);

        try {
            $result = $this->stock->adjust($product, (int) $data['adjustment']);
        } catch (DomainException $e) {
            return back()->withErrors([
                'adjustment' => $e->getMessage(),
            ])->withInput();
        }

        return to_route('inventario', ['product_id' => $product->id])
            ->with('success', 'Ajuste registrado. Estoque: '.$result['stock'].' ('.($result['adjustment'] > 0 ? '+' : '').$result['adjustment'].')');
    }
}
