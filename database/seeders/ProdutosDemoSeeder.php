<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProdutosDemoSeeder extends Seeder
{
    public function run(): void
    {
        $cats = [
            ['Escritório', '#6366f1'],
            ['Eletrônicos', '#10b981'],
            ['Limpeza', '#f59e0b'],
            ['Alimentos', '#f43f5e'],
            ['Ferramentas', '#0ea5e9'],
        ];
        foreach ($cats as [$n, $c]) {
            Category::updateOrCreate(['name' => $n], ['color' => $c]);
        }

        $data = [
            ['Caneta Bic Azul', 'ESC-0001', '7891000101017', 'Escritório', 120, 30, 'un'],
            ['Caneta Bic Preta', 'ESC-0002', '7891000101024', 'Escritório', 95, 30, 'un'],
            ['Papel Sulfite A4 500fls', 'ESC-0003', '7891000101031', 'Escritório', 8, 20, 'cx'],
            ['Clips n.2', 'ESC-0004', '7891000101048', 'Escritório', 40, 15, 'cx'],
            ['Mouse Óptico USB', 'ELE-0001', '7891000101055', 'Eletrônicos', 25, 5, 'un'],
            ['Teclado USB ABNT', 'ELE-0002', '7891000101062', 'Eletrônicos', 0, 5, 'un'],
            ['Cabo HDMI 1.5m', 'ELE-0003', '7891000101079', 'Eletrônicos', 60, 10, 'un'],
            ['Monitor 21.5" LED', 'ELE-0004', '7891000101086', 'Eletrônicos', 12, 4, 'un'],
            ['Álcool em Gel 500ml', 'LIM-0001', '7891000101093', 'Limpeza', 50, 12, 'un'],
            ['Detergente Neutro 500ml', 'LIM-0002', '7891000101109', 'Limpeza', 100, 20, 'un'],
            ['Papel Toalha Interfolhado', 'LIM-0003', '7891000101116', 'Limpeza', 18, 20, 'cx'],
            ['Sacola Plástica 25un', 'LIM-0004', '7891000101123', 'Limpeza', 300, 50, 'pc'],
            ['Café Torrado 500g', 'ALI-0001', '7891000101130', 'Alimentos', 35, 10, 'un'],
            ['Açúcar Refinado 1kg', 'ALI-0002', '7891000101147', 'Alimentos', 200, 40, 'un'],
            ['Água Mineral 500ml', 'ALI-0003', '7891000101154', 'Alimentos', 0, 24, 'fds'],
            ['Biscoito Cream Cracker', 'ALI-0004', '7891000101161', 'Alimentos', 70, 20, 'pc'],
            ['Chave de Fenda Set', 'FER-0001', '7891000101178', 'Ferramentas', 22, 6, 'un'],
            ['Alicate Universal', 'FER-0002', '7891000101185', 'Ferramentas', 5, 6, 'un'],
            ['Fita Isolante', 'FER-0003', '7891000101192', 'Ferramentas', 45, 10, 'pc'],
            ['Luvas de Látex 50un', 'FER-0004', '7891000101208', 'Ferramentas', 9, 12, 'cx'],
        ];

        foreach ($data as [$name, , $barcode, $cat, $stock, $min, $unit]) {
            Product::updateOrCreate(['barcode' => $barcode], [
                'name' => $name,
                'barcode' => $barcode,
                'category_id' => Category::where('name', $cat)->value('id'),
                'stock' => $stock,
                'min_stock' => $min,
                'unit' => $unit,
                'is_active' => true,
            ]);
        }
    }
}
