<?php

namespace App\Http\Controllers;

use App\Exports\ListaCompraExport;
use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
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

        $openProductIds = PurchaseRequestItem::whereHas('request', function ($q) {
            $q->whereIn('status', [PurchaseRequest::STATUS_PENDING, PurchaseRequest::STATUS_PARTIAL]);
        })->pluck('product_id');

        return view('lista-compra', ['products' => $products, 'pendingProductIds' => $openProductIds]);
    }

    public function export(): BinaryFileResponse
    {
        return Excel::download(new ListaCompraExport, 'lista-compra-' . now()->format('d-m-Y') . '.xlsx');
    }
}
