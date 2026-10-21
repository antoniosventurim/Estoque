<?php

namespace App\Http\Requests;

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
        ];
    }
}
