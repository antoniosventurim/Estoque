<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\PurchaseListItem;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ListaCompraExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    private int $row = 1;

    public function collection(): Collection
    {
        $manualItems = PurchaseListItem::with('product')
            ->get()
            ->filter(fn (PurchaseListItem $item) => $item->product !== null)
            ->values();
        $manualProductIds = $manualItems->pluck('product_id');

        $lowStock = Product::with('category', 'unitModel')
            ->whereColumn('stock', '<=', 'min_stock')
            ->whereNotIn('id', $manualProductIds)
            ->orderBy('name')
            ->get();

        return $lowStock
            ->map(fn (Product $product) => [
                'product' => $product,
                'quantity' => $product->quantity_to_order,
                'manual' => false,
            ])
            ->concat($manualItems->map(fn (PurchaseListItem $item) => [
                'product' => $item->product,
                'quantity' => $item->quantity,
                'manual' => true,
            ]))
            ->values();
    }

    public function headings(): array
    {
        return ['#', 'Produto', 'Categoria', 'Unidade', 'Estoque Atual', 'Estoque Mínimo', 'Estoque Máximo', 'Quantidade a Pedir', 'Origem'];
    }

    public function map($row): array
    {
        $product = $row['product'];

        return [
            $this->row++,
            $product->name,
            $product->category->name ?? '—',
            $product->unitModel->name ?? $product->unit,
            $product->stock,
            $product->min_stock,
            $product->max_stock,
            $row['quantity'],
            $row['manual'] ? 'Adicionado manualmente' : 'Abaixo do mínimo',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
