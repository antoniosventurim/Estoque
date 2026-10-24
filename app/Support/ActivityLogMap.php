<?php

namespace App\Support;

use App\Models\Category;
use App\Models\CostCenter;
use App\Models\Employee;
use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\PurchaseListItem;
use App\Models\Setting;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityLogMap
{
    /**
     * Label pt-BR dos modelos registrados no histórico.
     *
     * @var array<class-string<Model>, string>
     */
    public const SUBJECT_LABELS = [
        Category::class => 'Categoria',
        CostCenter::class => 'Centro de Custo',
        Employee::class => 'Funcionário',
        Product::class => 'Produto',
        PurchaseRequest::class => 'Solicitação de Compra',
        PurchaseRequestItem::class => 'Item de Solicitação',
        PurchaseListItem::class => 'Item da Lista de Compra',
        Setting::class => 'Configuração',
        Unit::class => 'Unidade de Medida',
        User::class => 'Usuário',
    ];

    public const EVENT_LABELS = [
        'created' => 'Criação',
        'updated' => 'Alteração',
        'deleted' => 'Exclusão',
        'restored' => 'Restauração',
    ];

    protected const EVENT_VERBS = [
        'created' => 'criou',
        'updated' => 'atualizou',
        'deleted' => 'excluiu',
        'restored' => 'restaurou',
    ];

    public static function labelForSubject(?string $class): string
    {
        return self::SUBJECT_LABELS[$class] ?? 'Registro';
    }

    public static function labelForEvent(?string $event): string
    {
        return self::EVENT_LABELS[$event] ?? ucfirst((string) $event);
    }

    public static function badgeTypeForEvent(?string $event): string
    {
        return match ($event) {
            'created' => 'success',
            'updated' => 'info',
            'deleted' => 'danger',
            default => 'neutral',
        };
    }

    public static function descriptionFor(string $event, Model $subject): string
    {
        $verb = self::EVENT_VERBS[$event] ?? $event;

        return $verb.' '.self::labelForSubject($subject::class);
    }

    /**
     * Descrição curta do registro afetado (ex.: "#12 — Coca-Cola").
     */
    public static function subjectDescription(?Model $subject, ?int $subjectId): string
    {
        $id = $subjectId !== null ? '#'.$subjectId : '—';

        if ($subject === null) {
            return $id;
        }

        $name = $subject->name ?? $subject->key ?? null;

        return $name !== null && $name !== '' ? "{$id} — {$name}" : $id;
    }

    /**
     * Lista de mudanças de um registro "updated" no formato
     * [['attribute' => ..., 'old' => ..., 'new' => ...], ...].
     *
     * @return array<int, array{attribute: string, old: mixed, new: mixed}>
     */
    public static function changes(object $activity): array
    {
        if ($activity->event !== 'updated') {
            return [];
        }

        $new = $activity->attribute_changes?->get('attributes') ?? [];
        $old = $activity->attribute_changes?->get('old') ?? [];

        $changes = [];

        foreach ((array) $new as $attribute => $newValue) {
            $oldValue = $old[$attribute] ?? null;

            if ($oldValue != $newValue) {
                $changes[] = [
                    'attribute' => (string) $attribute,
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }

    /**
     * Atributos de um registro "created" no formato [['attribute' => ..., 'value' => ...], ...].
     *
     * @return array<int, array{attribute: string, value: mixed}>
     */
    public static function createdAttributes(object $activity): array
    {
        if ($activity->event !== 'created') {
            return [];
        }

        $attributes = $activity->attribute_changes?->get('attributes') ?? [];

        $result = [];

        foreach ((array) $attributes as $attribute => $value) {
            $result[] = ['attribute' => (string) $attribute, 'value' => $value];
        }

        return $result;
    }

    public static function formatValue(mixed $value): string
    {
        return match (true) {
            $value === null, $value === '' => '—',
            $value === true, $value === 1, $value === '1' => 'Sim',
            $value === false, $value === 0, $value === '0' => 'Não',
            is_array($value) => json_encode($value, JSON_UNESCAPED_UNICODE),
            default => (string) $value,
        };
    }

    /**
     * @return array<int, array{id: string, name: string, sub: string}>
     */
    public static function eventOptions(): array
    {
        return collect(self::EVENT_LABELS)
            ->map(fn (string $label, string $value) => ['id' => $value, 'name' => $label, 'sub' => ''])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: class-string, name: string, sub: string}>
     */
    public static function subjectOptions(): array
    {
        return collect(self::SUBJECT_LABELS)
            ->map(fn (string $label, string $class) => ['id' => $class, 'name' => $label, 'sub' => ''])
            ->values()
            ->all();
    }
}
