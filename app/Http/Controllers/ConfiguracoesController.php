<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfiguracoesSaveRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
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
            'alerta_estoque_acima_minimo' => $settings['alerta_estoque_acima_minimo'] ?? 30,
        ]);
    }

    public function save(ConfiguracoesSaveRequest $request): RedirectResponse
    {
        Setting::dump($request->validated());

        return back()->with('success', 'Configurações salvas.');
    }
}
