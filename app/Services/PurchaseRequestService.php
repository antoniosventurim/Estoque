<?php

namespace App\Services;

use App\Exceptions\DomainException;
use App\Models\Movement;
use App\Models\Product;
use App\Models\PurchaseListItem;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use Illuminate\Support\Facades\DB;

class PurchaseRequestService
{
    /**
     * Gera uma solicitação de compra a partir dos produtos com estoque baixo
     * e dos itens adicionados manualmente à lista de compra.
     *
     * Ao criar a solicitação, os itens de `purchase_list_items` correspondentes
     * são consumidos (removidos) — o status volta a ser controlado pela solicitação.
     *
     * @param  array<int|string, int>  $quantities  quantidades personalizadas por product_id
     */
    public function generate(?string $note, array $quantities = []): PurchaseRequest
    {
        $openProductIds = PurchaseRequestItem::whereHas('request', function ($q) {
            $q->whereIn('status', [PurchaseRequest::STATUS_PENDING, PurchaseRequest::STATUS_PARTIAL]);
        })->pluck('product_id');

        $products = Product::with('unitModel')
            ->whereColumn('stock', '<=', 'min_stock')
            ->whereNotIn('id', $openProductIds)
            ->orderBy('name')
            ->get();

        // Itens manuais da lista (mesmo acima do mínimo); ignora produto excluído e pendente.
        $manualItems = PurchaseListItem::with('product')
            ->whereHas('product')
            ->get()
            ->reject(fn (PurchaseListItem $item) => $openProductIds->contains($item->product_id))
            ->keyBy('product_id');

        if ($products->isEmpty() && $manualItems->isEmpty()) {
            throw new DomainException('Nenhum produto novo para solicitar. Verifique as solicitações pendentes.');
        }

        $items = [];

        foreach ($products as $product) {
            $default = $this->defaultQuantity($product, $manualItems->get($product->id));

            $qty = array_key_exists($product->id, $quantities)
                ? min((int) $quantities[$product->id], $default)
                : $default;

            if ($qty <= 0) {
                continue;
            }

            $items[] = ['product_id' => $product->id, 'quantity' => $qty];
        }

        foreach ($manualItems as $item) {
            if (in_array($item->product_id, array_column($items, 'product_id'), true)) {
                continue;
            }

            $default = max(1, (int) $item->quantity);

            $qty = array_key_exists($item->product_id, $quantities)
                ? min((int) $quantities[$item->product_id], $default)
                : $default;

            if ($qty <= 0) {
                continue;
            }

            $items[] = ['product_id' => $item->product_id, 'quantity' => $qty];
        }

        if (empty($items)) {
            throw new DomainException('Nenhum item selecionado para solicitar. Informe ao menos uma quantidade maior que zero.');
        }

        return DB::transaction(function () use ($items, $note) {
            $purchaseRequest = PurchaseRequest::create([
                'user_id' => auth()->id(),
                'status' => PurchaseRequest::STATUS_PENDING,
                'note' => $note,
            ]);

            foreach ($items as $item) {
                $purchaseRequest->items()->create($item);
            }

            PurchaseListItem::whereIn('product_id', array_column($items, 'product_id'))
                ->get()
                ->each->delete();

            return $purchaseRequest;
        });
    }

    /**
     * Recebe uma solicitação (total ou parcial) e atualiza o estoque.
     *
     * @param  array<int|string, int>|null  $receivedInput  quantidades recebidas por item id
     * @return bool true se o recebimento ficou parcial
     */
    public function deliver(PurchaseRequest $purchaseRequest, ?array $receivedInput = null): bool
    {
        if (! $purchaseRequest->isReceivable()) {
            throw new DomainException('Esta solicitação já foi recebida.');
        }

        $purchaseRequest->load('items.product');
        $hasPartialInput = $receivedInput !== null;
        $receivedInput = $receivedInput ?? [];

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

        return $partial;
    }

    public function destroy(PurchaseRequest $purchaseRequest): void
    {
        if (! $purchaseRequest->isPending()) {
            throw new DomainException('Solicitações recebidas não podem ser excluídas.');
        }

        $purchaseRequest->delete();
    }

    private function defaultQuantity(Product $product, ?PurchaseListItem $manualItem = null): int
    {
        if ($manualItem !== null) {
            return max(1, (int) $manualItem->quantity);
        }

        if ($product->quantity_to_order > 0) {
            return $product->quantity_to_order;
        }

        return max(1, $product->min_stock - $product->stock);
    }
}
