<?php

// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Config\BranchController;
use App\Http\Controllers\Config\ProductCategoryController;
use App\Http\Controllers\Config\SupplierController;
use App\Http\Controllers\Config\UnitController;
use App\Http\Controllers\Config\UnitConversionController;
use App\Http\Controllers\Config\WarehouseController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Product\ProductPriceController;
use App\Http\Controllers\Product\ProductWalletController;
use App\Http\Controllers\Product\ProductWarehouseController;
use App\Http\Controllers\Roles\RoleController;
use App\Http\Controllers\User\UserController;

Route::group([
    // 'middleware' => 'api',
    'prefix' => 'auth',
    // 'middleware' => ['auth:api', 'role:admin'], // tiene que tener rol admin
    // 'middleware' => ['auth:api', 'permission:edit articles'], // tiene que tener permiso para editar artículos
], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    // middleware para las rutas que requieren autenticación
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api')->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api')->name('refresh');
    Route::post('/me', [AuthController::class, 'me'])->middleware('auth:api')->name('me');
});

Route::group([
    // "middleware" => ["auth:api"]
], function ($router) {
    Route::resource("roles", RoleController::class);
    Route::get("users/config", [UserController::class, 'config']);
    Route::post("users/{id}", [UserController::class, 'update']);
    Route::resource("users", UserController::class);

    Route::resource("branches", BranchController::class);
    Route::get("warehouses/config", [WarehouseController::class, 'config']);
    Route::resource("warehouses", WarehouseController::class);

    Route::get("categories/config", [ProductCategoryController::class, 'config']);
    Route::post("categories/{id}", [ProductCategoryController::class, 'update']);
    Route::resource("categories", ProductCategoryController::class);

    // Route::get("suppliers/config", [SupplierController::class, 'config']);
    Route::post("suppliers/{id}", [SupplierController::class, 'update']);
    Route::resource("suppliers", SupplierController::class);

    Route::get("units/config", [UnitController::class, 'config']);
    Route::resource("units", UnitController::class);
    Route::resource("unit-conversions", UnitConversionController::class);

    Route::get("products/config", [ProductController::class, 'config']);
    Route::post("products/index", [ProductController::class, 'index']);
    Route::post("products/s3_image", [ProductController::class, 's3_image']);
    Route::post("products/{id}", [ProductController::class, 'update']);
    Route::resource("products", ProductController::class);
    Route::post("products-excel/export", [ProductController::class, 'download_excel']);
    Route::post("products-excel/import", [ProductController::class, 'import_excel']);

    Route::resource("product-warehouses", ProductWarehouseController::class);

    Route::resource("product-wallets", ProductWalletController::class);
    
    // Route::resource("product-prices", ProductPriceController::class);

});
