<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('unit') === null) {
            $this->merge(['unit' => 'un']);
        }

        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'barcode' => [
                'nullable', 'string', 'max:44',
                Rule::unique('products', 'barcode')->ignore($productId),
            ],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:2048'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'min_stock' => ['nullable', 'integer', 'min:0'],
            'max_stock' => ['nullable', 'integer', 'min:0'],
            'note' => ['nullable', 'string', 'max:500'],
            'unit' => ['nullable', 'string', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
            'expiration_date' => $this->requiresExpiration()
                ? ['required', 'date']
                : ['nullable', 'date'],
        ];
    }

    public function attributes(): array
    {
        return [
            'expiration_date' => 'data de validade',
        ];
    }

    public function messages(): array
    {
        return [
            'expiration_date.required' => 'A data de validade é obrigatória para produtos da categoria que exige validade.',
        ];
    }

    private function requiresExpiration(): bool
    {
        $categoryId = $this->input('category_id');

        if (! $categoryId) {
            return false;
        }

        $category = Category::find($categoryId);

        return (bool) ($category?->requires_expiration);
    }
}
