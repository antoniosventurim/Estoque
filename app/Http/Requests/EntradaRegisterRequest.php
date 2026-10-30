<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class EntradaRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'barcode' => ['required', 'string'],
            'quantity' => ['required', 'integer', 'min:1'],
            'cost_center_id' => ['nullable', 'exists:cost_centers,id'],
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
        $barcode = trim((string) $this->input('barcode', ''));

        if ($barcode === '') {
            return false;
        }

        $product = Product::where('barcode', $barcode)->first();

        return (bool) ($product?->category?->requires_expiration);
    }
}
