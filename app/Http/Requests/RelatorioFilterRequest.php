<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class RelatorioFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'from' => $this->normalizeDate($this->input('from')),
            'to' => $this->normalizeDate($this->input('to')),
        ]);
    }

    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date_format:d-m-Y', 'before_or_equal:to'],
            'to' => ['nullable', 'date_format:d-m-Y'],
        ];
    }

    public function messages(): array
    {
        return [
            'from.date_format' => 'A data inicial está em formato inválido (use dd-mm-aaaa).',
            'to.date_format' => 'A data final está em formato inválido (use dd-mm-aaaa).',
            'from.before_or_equal' => 'A data inicial não pode ser maior que a data final.',
        ];
    }

    public function fromDate(): ?string
    {
        return $this->parseDate('from');
    }

    public function toDate(): ?string
    {
        return $this->parseDate('to');
    }

    private function normalizeDate(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function parseDate(string $field): ?string
    {
        $value = $this->input($field);

        if (! is_string($value) || $value === '') {
            return null;
        }

        return Carbon::createFromFormat('d-m-Y', $value)->format('Y-m-d');
    }
}
