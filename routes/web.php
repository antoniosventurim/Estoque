<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConfiguracoesController;
use App\Http\Controllers\CostCenterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EtiquetaController;
use App\Http\Controllers\EmployeeController;
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
Route::middleware('auth')->group(function () {
    Route::get('/admin', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware('auth')->group(function () {

    // Produtos
    Route::get('/admin/produtos', [ProductController::class, 'index'])->middleware('can:produtos.view')->name('produtos.index');
    Route::get('/admin/produtos/novo', [ProductController::class, 'create'])->middleware('can:produtos.create')->name('produtos.create');
    Route::post('/admin/produtos', [ProductController::class, 'store'])->middleware('can:produtos.create')->name('produtos.store');
    Route::get('/admin/produtos/{product}/editar', [ProductController::class, 'edit'])->middleware('can:produtos.edit')->name('produtos.edit');
    Route::put('/admin/produtos/{product}', [ProductController::class, 'update'])->middleware('can:produtos.edit')->name('produtos.update');
    Route::delete('/admin/produtos/{product}', [ProductController::class, 'destroy'])->middleware('can:produtos.delete')->name('produtos.destroy');
    Route::post('/admin/produtos/bulk-destroy', [ProductController::class, 'bulkDestroy'])->middleware('can:produtos.delete')->name('produtos.bulkDestroy');
    Route::get('/admin/produtos/{product}/barcode', [BarcodeController::class, 'show'])->middleware('can:produtos.view')->name('produtos.barcode');

    // Scanner: saída / entrada / inventário
    Route::get('/admin/saida', [MovimentoController::class, 'saida'])->middleware('can:saida.view')->name('saida');
    Route::post('/admin/saida', [MovimentoController::class, 'saidaRegister'])->middleware('can:saida.create')->name('saida.register');
    Route::post('/admin/saida/batch', [MovimentoController::class, 'saidaBatch'])->middleware('can:saida.create')->name('saida.batch');
    Route::get('/admin/saida/search', [MovimentoController::class, 'searchProduct'])->middleware('can:saida.view')->name('saida.search');
    Route::get('/admin/entrada', [MovimentoController::class, 'entrada'])->middleware('can:entrada.view')->name('entrada');
    Route::post('/admin/entrada', [MovimentoController::class, 'entradaRegister'])->middleware('can:entrada.create')->name('entrada.register');
    Route::get('/admin/inventario', [InventarioController::class, 'index'])->middleware('can:inventario.view')->name('inventario');
    Route::post('/admin/inventario', [InventarioController::class, 'registerAdjust'])->middleware('can:inventario.create')->name('inventario.register');

    // Consultas
    Route::get('/admin/movimentacoes', [MovimentacoesController::class, 'index'])->middleware('can:movimentacoes.view')->name('movimentacoes');
    Route::get('/admin/relatorios', [RelatorioController::class, 'index'])->middleware('can:relatorios.view')->name('relatorios');
    Route::get('/admin/lista-compra', [ListaCompraController::class, 'index'])->middleware('can:lista-compra.view')->name('lista-compra.index');
    Route::get('/admin/lista-compra/export', [ListaCompraController::class, 'export'])->middleware('can:lista-compra.view')->name('lista-compra.export');

    // Solicitações de compra
    Route::get('/admin/solicitacoes', [PurchaseRequestController::class, 'index'])->middleware('can:solicitacoes.view')->name('solicitacoes.index');
    Route::post('/admin/solicitacoes', [PurchaseRequestController::class, 'store'])->middleware('can:solicitacoes.create')->name('solicitacoes.store');
    Route::get('/admin/solicitacoes/{purchaseRequest}', [PurchaseRequestController::class, 'show'])->middleware('can:solicitacoes.view')->name('solicitacoes.show');
    Route::post('/admin/solicitacoes/{purchaseRequest}/entregar', [PurchaseRequestController::class, 'deliver'])->middleware('can:solicitacoes.receive')->name('solicitacoes.deliver');
    Route::delete('/admin/solicitacoes/{purchaseRequest}', [PurchaseRequestController::class, 'destroy'])->middleware('can:solicitacoes.delete')->name('solicitacoes.destroy');

    // Centros de custo
    Route::get('/admin/centros-custo', [CostCenterController::class, 'index'])->middleware('can:centros-custo.view')->name('centros-custo.index');
    Route::get('/admin/centros-custo/novo', [CostCenterController::class, 'create'])->middleware('can:centros-custo.create')->name('centros-custo.create');
    Route::post('/admin/centros-custo', [CostCenterController::class, 'store'])->middleware('can:centros-custo.create')->name('centros-custo.store');
    Route::get('/admin/centros-custo/{costCenter}/editar', [CostCenterController::class, 'edit'])->middleware('can:centros-custo.edit')->name('centros-custo.edit');
    Route::put('/admin/centros-custo/{costCenter}', [CostCenterController::class, 'update'])->middleware('can:centros-custo.edit')->name('centros-custo.update');
    Route::delete('/admin/centros-custo/{costCenter}', [CostCenterController::class, 'destroy'])->middleware('can:centros-custo.delete')->name('centros-custo.destroy');

    // Categorias
    Route::get('/admin/categorias', [CategoryController::class, 'index'])->middleware('can:categorias.view')->name('categorias.index');
    Route::get('/admin/categorias/novo', [CategoryController::class, 'create'])->middleware('can:categorias.create')->name('categorias.create');
    Route::post('/admin/categorias', [CategoryController::class, 'store'])->middleware('can:categorias.create')->name('categorias.store');
    Route::get('/admin/categorias/{category}/editar', [CategoryController::class, 'edit'])->middleware('can:categorias.edit')->name('categorias.edit');
    Route::put('/admin/categorias/{category}', [CategoryController::class, 'update'])->middleware('can:categorias.edit')->name('categorias.update');
    Route::delete('/admin/categorias/{category}', [CategoryController::class, 'destroy'])->middleware('can:categorias.delete')->name('categorias.destroy');

    // Unidades
    Route::get('/admin/unidades', [UnitController::class, 'index'])->middleware('can:unidades.view')->name('unidades.index');
    Route::get('/admin/unidades/novo', [UnitController::class, 'create'])->middleware('can:unidades.create')->name('unidades.create');
    Route::post('/admin/unidades', [UnitController::class, 'store'])->middleware('can:unidades.create')->name('unidades.store');
    Route::get('/admin/unidades/{unit}/editar', [UnitController::class, 'edit'])->middleware('can:unidades.edit')->name('unidades.edit');
    Route::put('/admin/unidades/{unit}', [UnitController::class, 'update'])->middleware('can:unidades.edit')->name('unidades.update');
    Route::delete('/admin/unidades/{unit}', [UnitController::class, 'destroy'])->middleware('can:unidades.delete')->name('unidades.destroy');

    // Administração
    Route::get('/admin/usuarios', [UsuarioController::class, 'index'])->middleware('can:usuarios.view')->name('usuarios');
    Route::get('/admin/usuarios/novo', [UsuarioController::class, 'create'])->middleware('can:usuarios.create')->name('usuarios.create');
    Route::post('/admin/usuarios', [UsuarioController::class, 'store'])->middleware('can:usuarios.create')->name('usuarios.store');
    Route::get('/admin/usuarios/{user}/editar', [UsuarioController::class, 'edit'])->middleware('can:usuarios.edit')->name('usuarios.edit');
    Route::put('/admin/usuarios/{user}', [UsuarioController::class, 'update'])->middleware('can:usuarios.edit')->name('usuarios.update');
    Route::delete('/admin/usuarios/{user}', [UsuarioController::class, 'destroy'])->middleware('can:usuarios.delete')->name('usuarios.destroy');

    // Perfis e permissões
    Route::get('/admin/perfis', [RoleController::class, 'index'])->middleware('can:perfis.manage')->name('roles.index');
    Route::get('/admin/perfis/novo', [RoleController::class, 'create'])->middleware('can:perfis.manage')->name('roles.create');
    Route::post('/admin/perfis', [RoleController::class, 'store'])->middleware('can:perfis.manage')->name('roles.store');
    Route::get('/admin/perfis/{role}/editar', [RoleController::class, 'edit'])->middleware('can:perfis.manage')->name('roles.edit');
    Route::put('/admin/perfis/{role}', [RoleController::class, 'update'])->middleware('can:perfis.manage')->name('roles.update');
    Route::delete('/admin/perfis/{role}', [RoleController::class, 'destroy'])->middleware('can:perfis.manage')->name('roles.destroy');

    // Funcionários
    Route::get('/admin/funcionarios', [EmployeeController::class, 'index'])->middleware('can:funcionarios.view')->name('funcionarios.index');
    Route::get('/admin/funcionarios/novo', [EmployeeController::class, 'create'])->middleware('can:funcionarios.create')->name('funcionarios.create');
    Route::post('/admin/funcionarios', [EmployeeController::class, 'store'])->middleware('can:funcionarios.create')->name('funcionarios.store');
    Route::get('/admin/funcionarios/{employee}/editar', [EmployeeController::class, 'edit'])->middleware('can:funcionarios.edit')->name('funcionarios.edit');
    Route::put('/admin/funcionarios/{employee}', [EmployeeController::class, 'update'])->middleware('can:funcionarios.edit')->name('funcionarios.update');
    Route::delete('/admin/funcionarios/{employee}', [EmployeeController::class, 'destroy'])->middleware('can:funcionarios.delete')->name('funcionarios.destroy');

    Route::get('/admin/configuracoes', [ConfiguracoesController::class, 'index'])->middleware('can:configuracoes.manage')->name('configuracoes');
    Route::post('/admin/configuracoes', [ConfiguracoesController::class, 'save'])->middleware('can:configuracoes.manage')->name('configuracoes.save');

    // Etiquetas / código de barras
    Route::get('/admin/etiquetas', [EtiquetaController::class, 'index'])->middleware('can:etiquetas.view')->name('etiquetas.index');
    Route::get('/admin/etiquetas/imprimir', [EtiquetaController::class, 'print'])->middleware('can:etiquetas.view')->name('etiquetas.print');
});
