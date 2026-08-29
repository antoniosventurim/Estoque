<?php

namespace App\Http\Controllers;

use App\Models\Movement;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RelatorioController extends Controller
{
    public function index(Request $request): View
    {
        $from = (string) $request->query('from', '');
        $to = (string) $request->query('to', '');

        $fromDate = $from !== '' ? Carbon::createFromFormat('d-m-Y', $from)->format('Y-m-d') : null;
        $toDate = $to !== '' ? Carbon::createFromFormat('d-m-Y', $to)->format('Y-m-d') : null;

        $movementsQuery = Movement::query()
            ->when($fromDate, fn ($q) => $q->whereDate('created_at', '>=', $fromDate))
            ->when($toDate, fn ($q) => $q->whereDate('created_at', '<=', $toDate));

        $totalEntradas = (clone $movementsQuery)->where('type', Movement::TYPE_IN)->sum('quantity');
        $totalSaidas = (clone $movementsQuery)->where('type', Movement::TYPE_OUT)->sum('quantity');
        $totalAjustes = (clone $movementsQuery)->where('type', Movement::TYPE_ADJUST)->count();
        $totalMovimentacoes = (clone $movementsQuery)->count();

        $topExits = (clone $movementsQuery)
            ->where('type', Movement::TYPE_OUT)
            ->selectRaw('product_id, SUM(quantity) as total')
            ->groupBy('product_id')
            ->orderByDesc('total')
            ->get()
            ->mapWithKeys(fn ($m) => [$m->product_id => (int) $m->total]);

        $movements = (clone $movementsQuery)->with('product', 'employee')->get();

        $products = Product::with('category')->get();
        $top = $products
            ->map(fn (Product $p) => [
                'product' => $p,
                'total' => $topExits[$p->id] ?? 0,
            ])
            ->filter(fn ($r) => $r['total'] > 0)
            ->sortByDesc('total')
            ->take(8);

        $critical = $products->filter(fn (Product $p) => $p->stock <= $p->min_stock);
        $approaching = $products->filter(fn (Product $p) => $p->stock > $p->min_stock && $p->stock <= ceil($p->min_stock * 1.5));

        $byEmployee = $movements->groupBy(fn ($m) => $m->employee?->name ?? '—')
            ->map(fn ($g) => [
                'name' => $g->first()->employee?->name ?? '—',
                'entradas' => $g->where('type', Movement::TYPE_IN)->sum('quantity'),
                'saidas' => $g->where('type', Movement::TYPE_OUT)->sum('quantity'),
                'ops' => $g->count(),
            ])
            ->sortByDesc('ops')
            ->values();

        return view('relatorios', compact(
            'from',
            'to',
            'totalEntradas',
            'totalSaidas',
            'totalAjustes',
            'totalMovimentacoes',
            'top',
            'critical',
            'approaching',
            'byEmployee',
        ));
    }
}
