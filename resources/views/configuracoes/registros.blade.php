<x-app.layout>
    <x-app.page-header
        title="Registros de Atividade"
        subtitle="{{ $activities->total() }} registro(s) no histórico"
        icon="history"
    />

    @php $activeTab = 'registros'; @endphp

    <div class="mb-4 flex items-center gap-1.5">
        @foreach ([
            'geral' => ['label' => 'Geral', 'route' => route('configuracoes')],
            'registros' => ['label' => 'Registros de Atividade', 'route' => route('configuracoes.registros')],
        ] as $key => $tab)
            <a href="{{ $tab['route'] }}"
                @class([
                    'rounded-md border px-3 py-1.5 text-[14px] transition-colors',
                    'border-mv-border bg-mv-surface2 text-mv-text font-medium' => $activeTab === $key,
                    'border-mv-border text-mv-text-secondary hover:text-mv-text' => $activeTab !== $key,
                ])
            >{{ $tab['label'] }}</a>
        @endforeach
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('configuracoes.registros') }}" class="mb-4 flex flex-col gap-2.5 sm:flex-row sm:flex-wrap sm:items-center">
        <div class="relative w-full sm:min-w-[200px] sm:flex-1">
            <div class="absolute left-2.5 top-1/2 -translate-y-1/2 text-mv-text-muted"><x-app.icon name="search" :size="14" /></div>
            <input type="text" name="q" value="{{ $search }}" placeholder="Buscar por descrição ou usuário..."
                class="w-full rounded-md border border-mv-border bg-mv-surface py-2 pl-8 pr-2.5 text-[14px] text-mv-text outline-none focus:border-blue-600">
        </div>
        <div class="relative w-full sm:w-[180px]">
            <x-app.searchable-select
                name="event"
                placeholder="Ação"
                :selected="$event"
                :selected-label="$event !== '' ? \App\Support\ActivityLogMap::labelForEvent($event) : ''"
                :options="\App\Support\ActivityLogMap::eventOptions()"
            />
        </div>
        <div class="relative w-full sm:w-[180px]">
            <x-app.searchable-select
                name="subject"
                placeholder="Módulo"
                :selected="$subject"
                :selected-label="$subject !== '' ? \App\Support\ActivityLogMap::labelForSubject($subject) : ''"
                :options="\App\Support\ActivityLogMap::subjectOptions()"
            />
        </div>
        <div class="flex gap-2.5">
            <x-app.btn type="submit" icon="search" variant="secondary" class="flex-1 justify-center sm:flex-none">Filtrar</x-app.btn>
            @if ($search !== '' || $event !== '' || $subject !== '')
                <x-app.btn as="a" href="{{ route('configuracoes.registros') }}" variant="ghost" class="flex-1 justify-center sm:flex-none">Limpar</x-app.btn>
            @endif
        </div>
    </form>

    <x-app.card class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-[14px]">
                <thead>
                    <tr class="border-b border-mv-border bg-mv-surface2">
                        @foreach (['#', 'Data/Hora', 'Usuário', 'Ação', 'Registro', 'Alterações'] as $h)
                            <th class="whitespace-nowrap px-3.5 py-2.5 text-left text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activities as $i => $activity)
                        <tr class="border-b border-mv-border align-top hover:bg-white/[0.02]">
                            <td class="px-3.5 py-2.5 text-xs text-mv-text-muted">{{ $activities->firstItem() + $i }}</td>
                            <td class="mono whitespace-nowrap px-3.5 py-2.5 text-xs text-mv-text-secondary">{{ $activity->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="px-3.5 py-2.5 text-mv-text-secondary">{{ $activity->causer->name ?? 'Sistema' }}</td>
                            <td class="px-3.5 py-2.5">
                                <span @class([
                                    'rounded-full px-2 py-0.5 text-[13px] font-semibold',
                                    'bg-green-500/10 text-green-500' => $activity->event === 'created',
                                    'bg-blue-500/10 text-blue-400' => $activity->event === 'updated',
                                    'bg-red-500/10 text-red-400' => $activity->event === 'deleted',
                                    'bg-mv-surface2 text-mv-text-secondary' => ! in_array($activity->event, ['created', 'updated', 'deleted'], true),
                                ])>{{ \App\Support\ActivityLogMap::labelForEvent($activity->event) }}</span>
                            </td>
                            <td class="px-3.5 py-2.5">
                                <div class="font-medium text-mv-text">{{ \App\Support\ActivityLogMap::labelForSubject($activity->subject_type) }}</div>
                                <div class="text-xs text-mv-text-muted">{{ \App\Support\ActivityLogMap::subjectDescription($activity->subject, $activity->subject_id) }}</div>
                            </td>
                            <td class="px-3.5 py-2.5">
                                @php
                                    $changes = \App\Support\ActivityLogMap::changes($activity);
                                    $created = \App\Support\ActivityLogMap::createdAttributes($activity);
                                @endphp
                                @if ($activity->event === 'updated' && $changes !== [])
                                    @foreach ($changes as $change)
                                        <div class="text-xs leading-5 text-mv-text-secondary">
                                            <span class="font-medium text-mv-text">{{ $change['attribute'] }}</span>:
                                            <span class="text-mv-danger">{{ \App\Support\ActivityLogMap::formatValue($change['old']) }}</span>
                                            <span class="text-mv-text-muted">→</span>
                                            <span class="text-mv-success">{{ \App\Support\ActivityLogMap::formatValue($change['new']) }}</span>
                                        </div>
                                    @endforeach
                                @elseif ($activity->event === 'created' && $created !== [])
                                    @foreach ($created as $attr)
                                        <div class="text-xs leading-5 text-mv-text-secondary">
                                            <span class="font-medium text-mv-text">{{ $attr['attribute'] }}</span>:
                                            {{ \App\Support\ActivityLogMap::formatValue($attr['value']) }}
                                        </div>
                                    @endforeach
                                @else
                                    <span class="text-xs text-mv-text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-3.5 py-10 text-center text-[14px] text-mv-text-muted">Nenhum registro de atividade encontrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-app.pagination :paginator="$activities" />
    </x-app.card>
</x-app.layout>
