<?php

namespace App\Http\Controllers;

use App\Exports\ListaCompraExport;
use App\Http\Requests\PurchaseListAddRequest;
use App\Models\Product;
use App\Models\PurchaseListItem;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ListaCompraController extends Controller
{
    public function index(): View
    {
        $alertMargin = (int) Setting::get('alerta_estoque_acima_minimo', 30);

        $openProductIds = PurchaseRequestItem::whereHas('request', function ($q) {
            $q->whereIn('status', [PurchaseRequest::STATUS_PENDING, PurchaseRequest::STATUS_PARTIAL]);
        })->pluck('product_id');

        $manualItems = PurchaseListItem::with(['product.category', 'product.unitModel'])
            ->get()
            ->filter(fn (PurchaseListItem $item) => $item->product !== null)
            ->values();
        $manualProductIds = $manualItems->pluck('product_id');

        // Abaixo do mínimo (automática), sem duplicar os itens manuais da lista.
        $lowStock = Product::with('category', 'unitModel')
            ->whereColumn('stock', '<=', 'min_stock')
            ->whereNotIn('id', $manualProductIds)
            ->orderBy('name')
            ->get();

        $rows = $lowStock
            ->map(fn (Product $product) => ['product' => $product, 'manual' => null])
            ->concat($manualItems->map(fn (PurchaseListItem $item) => ['product' => $item->product, 'manual' => $item]))
            ->values();

        // Próximos ao mínimo, elegíveis à adição manual (exclui pendentes e já listados).
        $nearProducts = Product::with('category', 'unitModel')
            ->where('min_stock', '>', 0)
            ->whereColumn('stock', '>', 'min_stock')
            ->whereRaw('stock * 100 <= min_stock * (100 + ?)', [$alertMargin])
            ->whereNotIn('id', $openProductIds)
            ->whereNotIn('id', $manualProductIds)
            ->orderBy('name')
            ->get();

        // Itens do modal de geração: abaixo do mínimo (não pendentes) + manuais (não pendentes).
        $generateRows = $lowStock
            ->reject(fn (Product $product) => $openProductIds->contains($product->id))
            ->map(fn (Product $product) => [
                'product' => $product,
                'quantity' => $product->quantity_to_order > 0 ? $product->quantity_to_order : max(1, $product->min_stock - $product->stock),
                'manual' => false,
            ])
            ->concat(
                $manualItems
                    ->reject(fn (PurchaseListItem $item) => $openProductIds->contains($item->product_id))
                    ->map(fn (PurchaseListItem $item) => [
                        'product' => $item->product,
                        'quantity' => max(1, (int) $item->quantity),
                        'manual' => true,
                    ])
            )
            ->sortBy('product.name')
            ->values();

        return view('lista-compra', [
            'rows' => $rows,
            'pendingProductIds' => $openProductIds,
            'nearProducts' => $nearProducts,
            'generateRows' => $generateRows,
        ]);
    }

    public function addItems(PurchaseListAddRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $quantities = $data['quantities'];

        $listedIds = PurchaseListItem::whereIn('product_id', $data['product_ids'])->pluck('product_id');

        // Ignora abaixo do mínimo (entram automaticamente) e já presentes na lista.
        $candidates = Product::whereIn('id', $data['product_ids'])
            ->whereNotIn('id', $listedIds)
            ->whereColumn('stock', '>', 'min_stock')
            ->get();

        $added = 0;

        foreach ($candidates as $product) {
            $quantity = (int) ($quantities[$product->id] ?? 0);

            if ($quantity < 1) {
                continue;
            }

            PurchaseListItem::firstOrCreate(
                ['product_id' => $product->id],
                ['quantity' => $quantity, 'user_id' => auth()->id()]
            );

            $added++;
        }

        if ($added === 0) {
            return redirect()->route('lista-compra.index')->with('error', 'Nenhum item novo para adicionar.');
        }

        return redirect()->route('lista-compra.index')->with('status', "{$added} item(ns) adicionado(s) à lista de compra.");
    }

    public function destroyItem(int $id): RedirectResponse
    {
        PurchaseListItem::findOrFail($id)->delete();

        return redirect()->route('lista-compra.index')->with('status', 'Item removido da lista de compra.');
    }

    public function export(): BinaryFileResponse
    {
        return Excel::download(new ListaCompraExport, 'lista-compra-'.now()->format('d-m-Y').'.xlsx');
    }
}
