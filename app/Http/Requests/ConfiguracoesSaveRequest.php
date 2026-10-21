<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfiguracoesSaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'empresa_nome' => ['nullable', 'string', 'max:255'],
            'empresa_cnpj' => ['nullable', 'string', 'max:18'],
            'empresa_endereco' => ['nullable', 'string', 'max:500'],
            'empresa_telefone' => ['nullable', 'string', 'max:20'],
            'estoque_minimo_padrao' => ['nullable', 'integer', 'min:0'],
            'alerta_estoque_acima_minimo' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ];
    }
}
