<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));

        $query = Category::withCount('products');

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        $categories = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('categorias.index', compact('categories', 'search'));
    }

    public function create(): View
    {
        return view('categorias.form', ['category' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Category::create($data);

        return redirect()->route('categorias.index')->with('status', 'Categoria cadastrada.');
    }

    public function edit(Category $category): View
    {
        return view('categorias.form', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $this->validated($request, $category);

        $category->update($data);

        return redirect()->route('categorias.index')->with('status', 'Categoria atualizada.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('categorias.index')->with('status', 'Categoria excluída.');
    }

    protected function validated(Request $request, ?Category $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,' . optional($category)->id],
            'color' => ['nullable', 'string', 'max:7'],
        ]);
    }
}
