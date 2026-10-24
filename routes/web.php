<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConfiguracoesController;
use App\Http\Controllers\CostCenterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EtiquetaController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\ListaCompraController;
use App\Http\Controllers\MovimentacoesController;
use App\Http\Controllers\MovimentoController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

// Autenticação
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ── Área autenticada ─────────────────────────────────────────────────
// As permissões de cada rota ficam centralizadas em PermissionMap::ROUTE_PERMISSIONS
// e são aplicadas pelo middleware "permission".
Route::middleware(['auth', 'permission'])->group(function () {

    Route::get('/admin', [DashboardController::class, 'index'])->name('dashboard');

    // Produtos
    Route::get('/admin/produtos', [ProductController::class, 'index'])->name('produtos.index');
    Route::get('/admin/produtos/novo', [ProductController::class, 'create'])->name('produtos.create');
    Route::post('/admin/produtos', [ProductController::class, 'store'])->name('produtos.store');
    Route::get('/admin/produtos/{product}/editar', [ProductController::class, 'edit'])->name('produtos.edit');
    Route::put('/admin/produtos/{product}', [ProductController::class, 'update'])->name('produtos.update');
    Route::delete('/admin/produtos/{product}', [ProductController::class, 'destroy'])->name('produtos.destroy');
    Route::post('/admin/produtos/bulk-destroy', [ProductController::class, 'bulkDestroy'])->name('produtos.bulkDestroy');
    Route::get('/admin/produtos/{product}/barcode', [BarcodeController::class, 'show'])->name('produtos.barcode');

    // Saída / entrada / inventário
    Route::get('/admin/saida', [MovimentoController::class, 'saida'])->name('saida');
    Route::post('/admin/saida', [MovimentoController::class, 'saidaRegister'])->name('saida.register');
    Route::post('/admin/saida/batch', [MovimentoController::class, 'saidaBatch'])->name('saida.batch');
    Route::get('/admin/saida/search', [MovimentoController::class, 'searchProduct'])->name('saida.search');
    Route::get('/admin/entrada', [MovimentoController::class, 'entrada'])->name('entrada');
    Route::post('/admin/entrada', [MovimentoController::class, 'entradaRegister'])->name('entrada.register');
    Route::get('/admin/inventario', [InventarioController::class, 'index'])->name('inventario');
    Route::post('/admin/inventario', [InventarioController::class, 'registerAdjust'])->name('inventario.register');

    // Consultas
    Route::get('/admin/movimentacoes', [MovimentacoesController::class, 'index'])->name('movimentacoes');
    Route::get('/admin/relatorios', [RelatorioController::class, 'index'])->name('relatorios');
    Route::get('/admin/lista-compra', [ListaCompraController::class, 'index'])->name('lista-compra.index');
    Route::get('/admin/lista-compra/export', [ListaCompraController::class, 'export'])->name('lista-compra.export');
    Route::post('/admin/lista-compra/adicionar', [ListaCompraController::class, 'addItems'])->name('lista-compra.adicionar');
    Route::delete('/admin/lista-compra/itens/{id}', [ListaCompraController::class, 'destroyItem'])->whereNumber('id')->name('lista-compra.destroyItem');

    // Solicitações de compra
    Route::get('/admin/solicitacoes', [PurchaseRequestController::class, 'index'])->name('solicitacoes.index');
    Route::post('/admin/solicitacoes', [PurchaseRequestController::class, 'store'])->name('solicitacoes.store');
    Route::get('/admin/solicitacoes/{purchaseRequest}', [PurchaseRequestController::class, 'show'])->name('solicitacoes.show');
    Route::post('/admin/solicitacoes/{purchaseRequest}/entregar', [PurchaseRequestController::class, 'deliver'])->name('solicitacoes.deliver');
    Route::delete('/admin/solicitacoes/{purchaseRequest}', [PurchaseRequestController::class, 'destroy'])->name('solicitacoes.destroy');

    // Centros de custo
    Route::get('/admin/centros-custo', [CostCenterController::class, 'index'])->name('centros-custo.index');
    Route::get('/admin/centros-custo/novo', [CostCenterController::class, 'create'])->name('centros-custo.create');
    Route::post('/admin/centros-custo', [CostCenterController::class, 'store'])->name('centros-custo.store');
    Route::get('/admin/centros-custo/{costCenter}/editar', [CostCenterController::class, 'edit'])->name('centros-custo.edit');
    Route::put('/admin/centros-custo/{costCenter}', [CostCenterController::class, 'update'])->name('centros-custo.update');
    Route::delete('/admin/centros-custo/{costCenter}', [CostCenterController::class, 'destroy'])->name('centros-custo.destroy');

    // Categorias
    Route::get('/admin/categorias', [CategoryController::class, 'index'])->name('categorias.index');
    Route::get('/admin/categorias/novo', [CategoryController::class, 'create'])->name('categorias.create');
    Route::post('/admin/categorias', [CategoryController::class, 'store'])->name('categorias.store');
    Route::get('/admin/categorias/{category}/editar', [CategoryController::class, 'edit'])->name('categorias.edit');
    Route::put('/admin/categorias/{category}', [CategoryController::class, 'update'])->name('categorias.update');
    Route::delete('/admin/categorias/{category}', [CategoryController::class, 'destroy'])->name('categorias.destroy');

    // Unidades
    Route::get('/admin/unidades', [UnitController::class, 'index'])->name('unidades.index');
    Route::get('/admin/unidades/novo', [UnitController::class, 'create'])->name('unidades.create');
    Route::post('/admin/unidades', [UnitController::class, 'store'])->name('unidades.store');
    Route::get('/admin/unidades/{unit}/editar', [UnitController::class, 'edit'])->name('unidades.edit');
    Route::put('/admin/unidades/{unit}', [UnitController::class, 'update'])->name('unidades.update');
    Route::delete('/admin/unidades/{unit}', [UnitController::class, 'destroy'])->name('unidades.destroy');

    // Usuários
    Route::get('/admin/usuarios', [UsuarioController::class, 'index'])->name('usuarios');
    Route::get('/admin/usuarios/novo', [UsuarioController::class, 'create'])->name('usuarios.create');
    Route::post('/admin/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
    Route::get('/admin/usuarios/{user}/editar', [UsuarioController::class, 'edit'])->name('usuarios.edit');
    Route::put('/admin/usuarios/{user}', [UsuarioController::class, 'update'])->name('usuarios.update');
    Route::delete('/admin/usuarios/{user}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');

    // Perfis e permissões
    Route::get('/admin/perfis', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/admin/perfis/novo', [RoleController::class, 'create'])->name('roles.create');
    Route::post('/admin/perfis', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/admin/perfis/{role}/editar', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/admin/perfis/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/admin/perfis/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    // Funcionários
    Route::get('/admin/funcionarios', [EmployeeController::class, 'index'])->name('funcionarios.index');
    Route::get('/admin/funcionarios/novo', [EmployeeController::class, 'create'])->name('funcionarios.create');
    Route::post('/admin/funcionarios', [EmployeeController::class, 'store'])->name('funcionarios.store');
    Route::get('/admin/funcionarios/{employee}/editar', [EmployeeController::class, 'edit'])->name('funcionarios.edit');
    Route::put('/admin/funcionarios/{employee}', [EmployeeController::class, 'update'])->name('funcionarios.update');
    Route::delete('/admin/funcionarios/{employee}', [EmployeeController::class, 'destroy'])->name('funcionarios.destroy');

    // Configurações
    Route::get('/admin/configuracoes', [ConfiguracoesController::class, 'index'])->name('configuracoes');
    Route::get('/admin/configuracoes/registros', [ConfiguracoesController::class, 'registros'])->name('configuracoes.registros');
    Route::post('/admin/configuracoes', [ConfiguracoesController::class, 'save'])->name('configuracoes.save');

    // Etiquetas / código de barras
    Route::get('/admin/etiquetas', [EtiquetaController::class, 'index'])->name('etiquetas.index');
    Route::get('/admin/etiquetas/imprimir', [EtiquetaController::class, 'print'])->name('etiquetas.print');
});
