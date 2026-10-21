<?php

namespace App\Services;

use App\Exceptions\DomainException;
use App\Models\Movement;
use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use Illuminate\Support\Facades\DB;

class PurchaseRequestService
{
    /**
     * Gera uma solicitação de compra a partir dos produtos com estoque baixo.
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

        if ($products->isEmpty()) {
            throw new DomainException('Nenhum produto novo para solicitar. Verifique as solicitações pendentes.');
        }

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

    private function defaultQuantity(Product $product): int
    {
        if ($product->quantity_to_order > 0) {
            return $product->quantity_to_order;
        }

        return max(1, $product->min_stock - $product->stock);
    }
}
