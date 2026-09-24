<?php

namespace App\Http\Controllers;

use App\Exports\ListaCompraExport;
use App\Models\Product;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ListaCompraController extends Controller
{
    public function index(): View
    {
        $products = Product::with('category', 'unitModel')
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('name')
            ->get();

        return view('lista-compra', compact('products'));
    }

    public function export(): BinaryFileResponse
    {
        return Excel::download(new ListaCompraExport, 'lista-compra-' . now()->format('d-m-Y') . '.xlsx');
    }
}
