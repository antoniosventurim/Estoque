<?php

namespace App\Http\Controllers;

use App\Models\Movement;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $alertMargin = (int) Setting::get('alerta_estoque_acima_minimo', 50);

        $totalProducts = Product::count();
        $totalStock = Product::sum('stock');

        $lowStock = Product::with('category')
            ->whereColumn('stock', '<=', 'min_stock')
            ->get();

        $nearMinStock = Product::with('category')
            ->where('min_stock', '>', 0)
            ->whereColumn('stock', '>', 'min_stock')
            ->whereRaw('stock <= min_stock * (1 + ? / 100)', [$alertMargin])
            ->orderBy('stock')
            ->get();

        $todayOut = Movement::query()
            ->where('type', Movement::TYPE_OUT)
            ->whereDate('created_at', today())
            ->sum('quantity');

        $latestMovements = Movement::query()
            ->with('product', 'employee')
            ->latest()
            ->take(8)
            ->get();

        return view('dashboard', compact(
            'totalProducts',
            'totalStock',
            'lowStock',
            'nearMinStock',
            'todayOut',
            'latestMovements',
        ));
    }
}
