<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConfiguracoesController extends Controller
{
    public function index(): View
    {
        $settings = Setting::all()->pluck('value', 'key');

        return view('configuracoes', [
            'empresa_nome' => $settings['empresa_nome'] ?? 'Minha Empresa Ltda',
            'empresa_cnpj' => $settings['empresa_cnpj'] ?? '',
            'empresa_endereco' => $settings['empresa_endereco'] ?? '',
            'empresa_telefone' => $settings['empresa_telefone'] ?? '',
            'estoque_minimo_padrao' => $settings['estoque_minimo_padrao'] ?? '',
            'alerta_estoque_acima_minimo' => $settings['alerta_estoque_acima_minimo'] ?? 50,
        ]);
    }

    public function save(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'empresa_nome' => ['nullable', 'string', 'max:255'],
            'empresa_cnpj' => ['nullable', 'string', 'max:18'],
            'empresa_endereco' => ['nullable', 'string', 'max:500'],
            'empresa_telefone' => ['nullable', 'string', 'max:20'],
            'estoque_minimo_padrao' => ['nullable', 'integer', 'min:0'],
            'alerta_estoque_acima_minimo' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ]);

        Setting::dump($data);

        return back()->with('success', 'Configurações salvas.');
    }
}
