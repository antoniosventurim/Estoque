<?php

namespace App\Http\Controllers;

use App\Exceptions\DomainException;
use App\Http\Requests\EntradaRegisterRequest;
use App\Http\Requests\SaidaBatchRequest;
use App\Http\Requests\SaidaRegisterRequest;
use App\Models\CostCenter;
use App\Models\Employee;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MovimentoController extends Controller
{
    public function __construct(private readonly StockService $stock) {}

    public function saida(Request $request): View
    {
        $barcode = trim((string) $request->query('barcode'));
        $product = $barcode !== '' ? Product::where('barcode', $barcode)->first() : null;
        $notFound = $barcode !== '' && $product === null;
        $costCenters = CostCenter::where('is_active', true)->orderBy('type')->orderBy('name')->get();
        $employees = Employee::orderBy('name')->get();
        $products = $this->productOptions();

        return view('saida', compact('product', 'notFound', 'costCenters', 'employees', 'products'));
    }

    public function saidaRegister(SaidaRegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        try {
            $result = $this->stock->saida(
                $data['barcode'],
                (int) $data['quantity'],
                (int) $data['cost_center_id'],
                (int) $data['employee_id']
            );
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('saida')->with('success', [
            'name' => $result['product']->name,
            'qty' => $result['qty'],
            'before' => $result['before'],
            'after' => $result['after'],
            'min_stock' => $result['product']->min_stock,
            'employee' => $result['employee']->name,
        ]);
    }

    public function saidaBatch(SaidaBatchRequest $request): RedirectResponse
    {
        $result = $this->stock->saidaBatch($request->validated());

        if (! empty($result['errors'])) {
            return back()->with('error', implode(' ', $result['errors']));
        }

        return redirect()->route('saida')->with('success_batch', [
            'items' => $result['results'],
            'employee' => $result['employee']->name,
            'count' => count($result['results']),
        ]);
    }

    public function entrada(Request $request): View
    {
        $barcode = trim((string) $request->query('barcode'));
        $product = $barcode !== '' ? Product::where('barcode', $barcode)->first() : null;
        $notFound = $barcode !== '' && $product === null;
        $costCenters = CostCenter::where('is_active', true)->orderBy('type')->orderBy('name')->get();
        $products = $this->productOptions();

        return view('entrada', compact('product', 'notFound', 'costCenters', 'products'));
    }

    public function entradaRegister(EntradaRegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        try {
            $result = $this->stock->entrada(
                $data['barcode'],
                (int) $data['quantity'],
                StockService::costCenterId($data)
            );
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('entrada')->with('success', [
            'name' => $result['product']->name,
            'qty' => $result['qty'],
            'before' => $result['before'],
            'after' => $result['after'],
            'min_stock' => $result['product']->min_stock,
            'user' => auth()->user()->name,
        ]);
    }

    public function searchProduct(Request $request): JsonResponse
    {
        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $products = Product::with('unitModel')
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get()
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'barcode' => $p->barcode,
                'stock' => $p->stock,
                'min_stock' => $p->min_stock,
                'unit' => $p->unitModel?->name ?? $p->unit,
                'category' => $p->category?->name,
            ]);

        return response()->json($products);
    }

    private function productOptions(): array
    {
        return Product::with('unitModel')->orderBy('name')
            ->get()
            ->map(fn (Product $p) => [
                'id' => $p->barcode,
                'name' => $p->name,
                'sub' => 'Cod: '.$p->barcode,
                'stock' => $p->stock,
                'min_stock' => $p->min_stock,
                'unit' => $p->unitModel?->name ?? $p->unit,
            ])
            ->values()
            ->all();
    }
}
