<?php

namespace App\Exports;

use App\Models\Product;
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
        return Product::with('category', 'unitModel')
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('name')
            ->get();
    }

    public function headings(): array
    {
        return ['#', 'Produto', 'Categoria', 'Unidade', 'Estoque Atual', 'Estoque Mínimo', 'Estoque Máximo', 'Quantidade a Pedir'];
    }

    public function map($product): array
    {
        return [
            $this->row++,
            $product->name,
            $product->category->name ?? '—',
            $product->unitModel->name ?? $product->unit,
            $product->stock,
            $product->min_stock,
            $product->max_stock,
            $product->quantity_to_order,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
