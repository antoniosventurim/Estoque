<?php

namespace App\Support;

class PermissionMap
{
    public const ACTION_LABELS = [
        'view' => 'Visualizar',
        'create' => 'Criar',
        'edit' => 'Editar',
        'delete' => 'Excluir',
        'receive' => 'Receber',
        'manage' => 'Gerenciar',
    ];

    public const MODULES = [
        'produtos' => ['label' => 'Produtos', 'actions' => ['view', 'create', 'edit', 'delete']],
        'saida' => ['label' => 'Saída de Estoque', 'actions' => ['view', 'create']],
        'entrada' => ['label' => 'Entrada de Estoque', 'actions' => ['view', 'create']],
        'inventario' => ['label' => 'Inventário', 'actions' => ['view', 'create']],
        'movimentacoes' => ['label' => 'Movimentações', 'actions' => ['view']],
        'relatorios' => ['label' => 'Relatórios', 'actions' => ['view']],
        'lista-compra' => ['label' => 'Lista de Compra', 'actions' => ['view']],
        'solicitacoes' => ['label' => 'Solicitações de Compra', 'actions' => ['view', 'create', 'receive', 'delete']],
        'centros-custo' => ['label' => 'Centros de Custo', 'actions' => ['view', 'create', 'edit', 'delete']],
        'categorias' => ['label' => 'Categorias', 'actions' => ['view', 'create', 'edit', 'delete']],
        'unidades' => ['label' => 'Unidades de Medida', 'actions' => ['view', 'create', 'edit', 'delete']],
        'funcionarios' => ['label' => 'Funcionários', 'actions' => ['view', 'create', 'edit', 'delete']],
        'usuarios' => ['label' => 'Usuários', 'actions' => ['view', 'create', 'edit', 'delete']],
        'etiquetas' => ['label' => 'Etiquetas', 'actions' => ['view']],
        'configuracoes' => ['label' => 'Configurações', 'actions' => ['manage']],
        'perfis' => ['label' => 'Permissões e Perfis', 'actions' => ['manage']],
    ];

    public const OPERATOR_PERMISSIONS = [
        'produtos.view',
        'saida.view',
        'saida.create',
        'entrada.view',
        'entrada.create',
        'inventario.view',
        'movimentacoes.view',
        'relatorios.view',
        'lista-compra.view',
        'solicitacoes.view',
        'etiquetas.view',
    ];

    /**
     * @return array<string, string> permission => label
     */
    public static function all(): array
    {
        $permissions = [];

        foreach (self::MODULES as $module => $config) {
            foreach ($config['actions'] as $action) {
                $permissions[$module.'.'.$action] = $config['label'].' — '.(self::ACTION_LABELS[$action] ?? $action);
            }
        }

        return $permissions;
    }

    public static function syncPermissions(): void
    {
        foreach (array_keys(self::all()) as $permission) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
