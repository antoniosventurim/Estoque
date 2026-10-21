<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaidaBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $raw = $this->input('items', '[]');
        $items = is_string($raw) ? json_decode($raw, true) : $raw;

        if (is_array($items)) {
            $this->merge(['items' => $items]);
        }
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.barcode' => ['required', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'cost_center_id' => ['required', 'exists:cost_centers,id'],
            'employee_id' => ['required', 'exists:employees,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Adicione pelo menos um item à lista.',
            'items.min' => 'Adicione pelo menos um item à lista.',
        ];
    }
}
