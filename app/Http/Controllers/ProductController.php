<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
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
        $categories = Category::orderBy('name')->get();
        $units = \App\Models\Unit::orderBy('name')->get();
        $barcode = Product::generateUniqueBarcode();

        return view('produtos.form', ['product' => null, 'categories' => $categories, 'units' => $units, 'generatedBarcode' => $barcode]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if (empty($data['barcode'])) {
            $data['barcode'] = Product::generateUniqueBarcode();
        }

        $data['image'] = $this->storeImage($request);

        Product::create($data);

        return redirect()->route('produtos.index')->with('status', 'Produto cadastrado.');
    }

    public function edit(Product $product): View
    {
        $categories = Category::orderBy('name')->get();
        $units = \App\Models\Unit::orderBy('name')->get();

        return view('produtos.form', compact('product', 'categories', 'units'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->boolean('remove_image')) {
            $this->deleteImage($product);
            $data['image'] = null;
        } else {
            $data['image'] = $this->storeImage($request) ?? $product->image;
        }

        if ($data['image'] !== $product->image) {
            $this->deleteImage($product);
        }

        $product->update($data);

        return redirect()->route('produtos.index')->with('status', 'Produto atualizado.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->deleteImage($product);
        $product->delete();

        return redirect()->route('produtos.index')->with('status', 'Produto excluído.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:products,id'],
        ]);

        $products = Product::whereIn('id', $data['ids'])->get();
        $total = $products->count();

        foreach ($products as $product) {
            $this->deleteImage($product);
            $product->delete();
        }

        return redirect()->route('produtos.index')->with('status', "{$total} produto(s) excluído(s).");
    }

    protected function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $path = $request->file('image')->store('produtos', 'public');

        return $path;
    }

    protected function deleteImage(Product $product): void
    {
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:44'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:2048'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'min_stock' => ['nullable', 'integer', 'min:0'],
            'unit' => ['nullable', 'string', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
        ]) + [
            'unit' => $request->input('unit', 'un'),
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
