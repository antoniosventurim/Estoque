<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfiguracoesSaveRequest;
use App\Models\Setting;
use App\Support\ActivityLogMap;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ConfiguracoesController extends Controller
{
    public function index(): View
    {
        $settings = Setting::all()->pluck('value', 'key');

        return view('configuracoes.index', [
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

    public function registros(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $event = (string) $request->query('event');
        $subject = (string) $request->query('subject');

        $query = Activity::query()
            ->with(['causer', 'subject'])
            ->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhereHas('causer', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if (array_key_exists($event, ActivityLogMap::EVENT_LABELS)) {
            $query->where('event', $event);
        }

        if (array_key_exists($subject, ActivityLogMap::SUBJECT_LABELS)) {
            $query->where('subject_type', $subject);
        }

        return view('configuracoes.registros', [
            'activities' => $query->paginate(15)->withQueryString(),
            'search' => $search,
            'event' => $event,
            'subject' => $subject,
        ]);
    }
}
