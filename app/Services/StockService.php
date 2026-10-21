<?php

namespace App\Services;

use App\Exceptions\DomainException;
use App\Models\Employee;
use App\Models\Movement;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Registra saída de estoque (desconta estoque + cria movimento).
     *
     * @return array{product: Product, qty: int, before: int, after: int, employee: Employee}
     */
    public function saida(string $barcode, int $quantity, int $costCenterId, int $employeeId): array
    {
        $product = Product::where('barcode', $barcode)->first();

        if (! $product) {
            throw new DomainException('Produto não encontrado.');
        }

        $before = $product->stock;

        if ($quantity > $before) {
            throw new DomainException('Quantidade solicitada maior que o estoque disponível.');
        }

        $employee = Employee::findOrFail($employeeId);
        $product->update(['stock' => $before - $quantity]);

        Movement::create([
            'product_id' => $product->id,
            'type' => Movement::TYPE_OUT,
            'quantity' => $quantity,
            'stock_before' => $before,
            'stock_after' => $product->stock,
            'user_id' => auth()->id(),
            'employee_id' => $employee->id,
            'cost_center_id' => $costCenterId,
        ]);

        return [
            'product' => $product,
            'qty' => $quantity,
            'before' => $before,
            'after' => $product->stock,
            'employee' => $employee,
        ];
    }

    /**
     * Registra saída em lote. Itens válidos são processados em transação;
     * itens inválidos geram mensagens de erro (comportamento original).
     *
     * @return array{results: array<int, array>, errors: array<int, string>, employee: Employee}
     */
    public function saidaBatch(array $data): array
    {
        $employee = Employee::findOrFail((int) $data['employee_id']);
        $costCenterId = self::costCenterId($data);
        $results = [];
        $errors = [];

        DB::transaction(function () use ($data, $employee, $costCenterId, &$results, &$errors) {
            foreach ($data['items'] as $item) {
                $product = Product::where('barcode', $item['barcode'])->first();

                if (! $product) {
                    $errors[] = "Produto com código {$item['barcode']} não encontrado.";

                    continue;
                }

                $quantity = (int) $item['quantity'];
                $before = $product->stock;

                if ($quantity > $before) {
                    $errors[] = "{$product->name}: estoque insuficiente ({$before} disponível).";

                    continue;
                }

                $product->update(['stock' => $before - $quantity]);

                Movement::create([
                    'product_id' => $product->id,
                    'type' => Movement::TYPE_OUT,
                    'quantity' => $quantity,
                    'stock_before' => $before,
                    'stock_after' => $product->stock,
                    'user_id' => auth()->id(),
                    'employee_id' => $employee->id,
                    'cost_center_id' => $costCenterId,
                ]);

                $results[] = [
                    'name' => $product->name,
                    'qty' => $quantity,
                    'before' => $before,
                    'after' => $product->stock,
                    'min_stock' => $product->min_stock,
                ];
            }
        });

        return ['results' => $results, 'errors' => $errors, 'employee' => $employee];
    }

    /**
     * Registra entrada de estoque (soma estoque + cria movimento).
     *
     * @return array{product: Product, qty: int, before: int, after: int}
     */
    public function entrada(string $barcode, int $quantity, ?int $costCenterId = null): array
    {
        $product = Product::where('barcode', $barcode)->first();

        if (! $product) {
            throw new DomainException('Produto não encontrado.');
        }

        $before = $product->stock;
        $product->update(['stock' => $before + $quantity]);

        Movement::create([
            'product_id' => $product->id,
            'type' => Movement::TYPE_IN,
            'quantity' => $quantity,
            'stock_before' => $before,
            'stock_after' => $product->stock,
            'user_id' => auth()->id(),
            'cost_center_id' => $costCenterId,
        ]);

        return [
            'product' => $product,
            'qty' => $quantity,
            'before' => $before,
            'after' => $product->stock,
        ];
    }

    /**
     * Aplica ajuste de inventário (sobra/quebra).
     *
     * @return array{product: Product, adjustment: int, stock: int}
     */
    public function adjust(Product $product, int $adjustment): array
    {
        $before = $product->stock;
        $newStock = $before + $adjustment;

        if ($newStock < 0) {
            throw new DomainException(
                'Ajuste não permitido. Estoque atual: '.$before.'. Não é possível reduzir mais que '.$before.' unidades.'
            );
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

        return [
            'product' => $product,
            'adjustment' => $adjustment,
            'stock' => $newStock,
        ];
    }

    public static function costCenterId(array $data): ?int
    {
        $id = $data['cost_center_id'] ?? null;

        return $id !== null && $id !== '' ? (int) $id : null;
    }
}
