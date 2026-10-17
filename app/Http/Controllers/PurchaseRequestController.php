<?php

namespace App\Http\Controllers;

use App\Models\Movement;
use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseRequestController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $status = (string) $request->query('status', 'all');

        $query = PurchaseRequest::with(['user', 'items.product'])
            ->withCount('items')
            ->orderByDesc('id');

        if (in_array($status, [PurchaseRequest::STATUS_PENDING, PurchaseRequest::STATUS_PARTIAL, PurchaseRequest::STATUS_DELIVERED], true)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->whereHas('items.product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $requests = $query->paginate(10)->withQueryString();

        $pendingCount = PurchaseRequest::where('status', PurchaseRequest::STATUS_PENDING)->count();
        $partialCount = PurchaseRequest::where('status', PurchaseRequest::STATUS_PARTIAL)->count();
        $deliveredCount = PurchaseRequest::where('status', PurchaseRequest::STATUS_DELIVERED)->count();

        return view('solicitacoes.index', compact('requests', 'search', 'status', 'pendingCount', 'partialCount', 'deliveredCount'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:255'],
            'quantities' => ['nullable', 'array'],
            'quantities.*' => ['nullable', 'integer', 'min:0'],
        ]);

        $openProductIds = PurchaseRequestItem::whereHas('request', function ($q) {
            $q->whereIn('status', [PurchaseRequest::STATUS_PENDING, PurchaseRequest::STATUS_PARTIAL]);
        })->pluck('product_id');

        $products = Product::with('unitModel')
            ->whereColumn('stock', '<=', 'min_stock')
            ->whereNotIn('id', $openProductIds)
            ->orderBy('name')
            ->get();

        if ($products->isEmpty()) {
            return back()->with('error', 'Nenhum produto novo para solicitar. Verifique as solicitações pendentes.');
        }

        $quantities = $data['quantities'] ?? [];
        $items = [];

        foreach ($products as $product) {
            $default = $this->defaultQuantity($product);

            $qty = array_key_exists($product->id, $quantities)
                ? min((int) $quantities[$product->id], $default)
                : $default;

            if ($qty <= 0) {
                continue;
            }

            $items[] = ['product_id' => $product->id, 'quantity' => $qty];
        }

        if (empty($items)) {
            return back()->with('error', 'Nenhum item selecionado para solicitar. Informe ao menos uma quantidade maior que zero.');
        }

        DB::transaction(function () use ($items, $data) {
            $purchaseRequest = PurchaseRequest::create([
                'user_id' => auth()->id(),
                'status' => PurchaseRequest::STATUS_PENDING,
                'note' => $data['note'] ?? null,
            ]);

            foreach ($items as $item) {
                $purchaseRequest->items()->create($item);
            }
        });

        return redirect()->route('solicitacoes.index')->with('status', 'Solicitação de compra criada com sucesso.');
    }

    private function defaultQuantity(Product $product): int
    {
        if ($product->quantity_to_order > 0) {
            return $product->quantity_to_order;
        }

        return max(1, $product->min_stock - $product->stock);
    }

    public function show(PurchaseRequest $purchaseRequest): View
    {
        $purchaseRequest->load(['user', 'deliveredBy', 'items.product.category', 'items.product.unitModel']);

        return view('solicitacoes.show', compact('purchaseRequest'));
    }

    public function deliver(Request $request, PurchaseRequest $purchaseRequest): RedirectResponse
    {
        if (! $purchaseRequest->isReceivable()) {
            return back()->with('error', 'Esta solicitação já foi recebida.');
        }

        $data = $request->validate([
            'received' => ['nullable', 'array'],
            'received.*' => ['nullable', 'integer', 'min:0'],
        ]);

        $purchaseRequest->load('items.product');
        $receivedInput = $data['received'] ?? [];
        $hasPartialInput = $request->has('received');

        $partial = false;

        DB::transaction(function () use ($purchaseRequest, $receivedInput, $hasPartialInput, &$partial) {
            foreach ($purchaseRequest->items as $item) {
                $missing = $item->missing_quantity;

                if ($missing <= 0) {
                    continue;
                }

                $qty = $hasPartialInput
                    ? min((int) ($receivedInput[$item->id] ?? 0), $missing)
                    : $missing;

                if ($qty <= 0) {
                    $partial = true;

                    continue;
                }

                $product = $item->product;
                $before = $product->stock;
                $product->update(['stock' => $before + $qty]);

                Movement::create([
                    'product_id' => $product->id,
                    'type' => Movement::TYPE_IN,
                    'quantity' => $qty,
                    'stock_before' => $before,
                    'stock_after' => $product->stock,
                    'user_id' => auth()->id(),
                ]);

                $item->update(['received_quantity' => $item->received_quantity + $qty]);

                if ($item->missing_quantity > 0) {
                    $partial = true;
                }
            }

            $complete = $purchaseRequest->items->fresh()->every(fn (PurchaseRequestItem $item) => $item->isFullyReceived());

            $purchaseRequest->update([
                'status' => $complete ? PurchaseRequest::STATUS_DELIVERED : PurchaseRequest::STATUS_PARTIAL,
                'delivered_at' => $complete ? ($purchaseRequest->delivered_at ?? now()) : $purchaseRequest->delivered_at,
                'delivered_by' => $complete ? auth()->id() : $purchaseRequest->delivered_by,
            ]);
        });

        if ($partial) {
            return redirect()->route('solicitacoes.show', $purchaseRequest)->with('status', 'Recebimento parcial registrado. Itens faltantes continuam pendentes.');
        }

        return redirect()->route('solicitacoes.index')->with('status', 'Material recebido. Estoque atualizado automaticamente.');
    }

    public function destroy(PurchaseRequest $purchaseRequest): RedirectResponse
    {
        if (! $purchaseRequest->isPending()) {
            return back()->with('error', 'Solicitações recebidas não podem ser excluídas.');
        }

        $purchaseRequest->delete();

        return redirect()->route('solicitacoes.index')->with('status', 'Solicitação excluída.');
    }
}
