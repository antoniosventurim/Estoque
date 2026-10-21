<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductBulkDestroyRequest;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $status = (string) $request->query('status', '');
        $categoryId = (string) $request->query('category', '');

        $query = Product::query()->with('category');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($status === 'ativo' || $status === 'inativo') {
            $query->where('is_active', $status === 'ativo');
        }

        if ($categoryId !== '') {
            $query->where('category_id', $categoryId);
        }

        $products = $query->paginate(10)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('produtos.index', compact('products', 'categories', 'search', 'status', 'categoryId'));
    }

    public function create(): View
    {
        return view('produtos.form', [
            'product' => null,
            'categories' => Category::orderBy('name')->get(),
            'units' => Unit::orderBy('name')->get(),
            'generatedBarcode' => Product::generateUniqueBarcode(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $this->products->create($request->validated(), $request->file('image'));

        return redirect()->route('produtos.index')->with('status', 'Produto cadastrado.');
    }

    public function edit(Product $product): View
    {
        return view('produtos.form', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
            'units' => Unit::orderBy('name')->get(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $this->products->update(
            $product,
            $request->validated(),
            $request->file('image'),
            $request->boolean('remove_image')
        );

        return redirect()->route('produtos.index')->with('status', 'Produto atualizado.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->products->destroy($product);

        return redirect()->route('produtos.index')->with('status', 'Produto excluído.');
    }

    public function bulkDestroy(ProductBulkDestroyRequest $request): RedirectResponse
    {
        $total = $this->products->destroyMany($request->validated('ids'));

        return redirect()->route('produtos.index')->with('status', "{$total} produto(s) excluído(s).");
    }
}
