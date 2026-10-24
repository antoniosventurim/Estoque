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
        'lista-compra' => ['label' => 'Lista de Compra', 'actions' => ['view', 'create', 'delete']],
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
        'lista-compra.create',
        'lista-compra.delete',
        'solicitacoes.view',
        'etiquetas.view',
    ];

    /**
     * Permissão exigida por rota (nome da rota => permissão).
     * Rotas ausentes não exigem permissão (ex: dashboard, login, logout).
     */
    public const ROUTE_PERMISSIONS = [
        // Produtos
        'produtos.index' => 'produtos.view',
        'produtos.create' => 'produtos.create',
        'produtos.store' => 'produtos.create',
        'produtos.edit' => 'produtos.edit',
        'produtos.update' => 'produtos.edit',
        'produtos.destroy' => 'produtos.delete',
        'produtos.bulkDestroy' => 'produtos.delete',
        'produtos.barcode' => 'produtos.view',

        // Saída / entrada / inventário
        'saida' => 'saida.view',
        'saida.register' => 'saida.create',
        'saida.batch' => 'saida.create',
        'saida.search' => 'saida.view',
        'entrada' => 'entrada.view',
        'entrada.register' => 'entrada.create',
        'inventario' => 'inventario.view',
        'inventario.register' => 'inventario.create',

        // Consultas
        'movimentacoes' => 'movimentacoes.view',
        'relatorios' => 'relatorios.view',
        'lista-compra.index' => 'lista-compra.view',
        'lista-compra.export' => 'lista-compra.view',
        'lista-compra.adicionar' => 'lista-compra.create',
        'lista-compra.destroyItem' => 'lista-compra.delete',

        // Solicitações de compra
        'solicitacoes.index' => 'solicitacoes.view',
        'solicitacoes.store' => 'solicitacoes.create',
        'solicitacoes.show' => 'solicitacoes.view',
        'solicitacoes.deliver' => 'solicitacoes.receive',
        'solicitacoes.destroy' => 'solicitacoes.delete',

        // Centros de custo
        'centros-custo.index' => 'centros-custo.view',
        'centros-custo.create' => 'centros-custo.create',
        'centros-custo.store' => 'centros-custo.create',
        'centros-custo.edit' => 'centros-custo.edit',
        'centros-custo.update' => 'centros-custo.edit',
        'centros-custo.destroy' => 'centros-custo.delete',

        // Categorias
        'categorias.index' => 'categorias.view',
        'categorias.create' => 'categorias.create',
        'categorias.store' => 'categorias.create',
        'categorias.edit' => 'categorias.edit',
        'categorias.update' => 'categorias.edit',
        'categorias.destroy' => 'categorias.delete',

        // Unidades
        'unidades.index' => 'unidades.view',
        'unidades.create' => 'unidades.create',
        'unidades.store' => 'unidades.create',
        'unidades.edit' => 'unidades.edit',
        'unidades.update' => 'unidades.edit',
        'unidades.destroy' => 'unidades.delete',

        // Usuários
        'usuarios' => 'usuarios.view',
        'usuarios.create' => 'usuarios.create',
        'usuarios.store' => 'usuarios.create',
        'usuarios.edit' => 'usuarios.edit',
        'usuarios.update' => 'usuarios.edit',
        'usuarios.destroy' => 'usuarios.delete',

        // Perfis e permissões
        'roles.index' => 'perfis.manage',
        'roles.create' => 'perfis.manage',
        'roles.store' => 'perfis.manage',
        'roles.edit' => 'perfis.manage',
        'roles.update' => 'perfis.manage',
        'roles.destroy' => 'perfis.manage',

        // Funcionários
        'funcionarios.index' => 'funcionarios.view',
        'funcionarios.create' => 'funcionarios.create',
        'funcionarios.store' => 'funcionarios.create',
        'funcionarios.edit' => 'funcionarios.edit',
        'funcionarios.update' => 'funcionarios.edit',
        'funcionarios.destroy' => 'funcionarios.delete',

        // Configurações / etiquetas
        'configuracoes' => 'configuracoes.manage',
        'configuracoes.registros' => 'configuracoes.manage',
        'configuracoes.save' => 'configuracoes.manage',
        'etiquetas.index' => 'etiquetas.view',
        'etiquetas.print' => 'etiquetas.view',
    ];

    public static function permissionForRoute(?string $routeName): ?string
    {
        return $routeName !== null ? (self::ROUTE_PERMISSIONS[$routeName] ?? null) : null;
    }

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
