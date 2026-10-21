<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequestDeliverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'received' => ['nullable', 'array'],
            'received.*' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
