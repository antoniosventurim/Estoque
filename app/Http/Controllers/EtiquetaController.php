<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EtiquetaController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $sort = in_array($request->query('sort'), ['name', 'barcode', 'stock'], true) ? $request->query('sort') : 'name';
        $dir = $request->query('dir') === 'desc' ? 'desc' : 'asc';

        $products = Product::query()
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%"))
            ->orderBy($sort, $dir)
            ->get();

        return view('etiquetas.index', compact('search', 'products', 'sort', 'dir'));
    }

    public function print(Request $request): View
    {
        $ids = array_map('intval', (array) $request->query('ids', []));
        $ids = array_values(array_filter($ids));

        $products = $ids !== []
            ? Product::whereIn('id', $ids)->orderBy('name')->get()
            : collect();

        return view('etiquetas.print', compact('products'));
    }
}
