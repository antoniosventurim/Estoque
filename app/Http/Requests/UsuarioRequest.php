<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_admin' => $this->boolean('is_admin')]);
    }

    public function rules(): array
    {
        $passwordRules = $this->isUpdating()
            ? ['nullable', 'confirmed', 'min:8']
            : ['required', 'confirmed', 'min:8'];

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$this->route('user')?->id],
            'password' => $passwordRules,
            'is_admin' => ['nullable', 'boolean'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);

        if ($this->isUpdating() && empty($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }

    private function isUpdating(): bool
    {
        return $this->route('user') !== null;
    }
}
