<?php

use App\Http\Controllers\BuyMaterial\BuyMaterialController;
use App\Http\Controllers\ApiAuthorization\LoginController;
use App\Http\Controllers\Materials\MaterialController;
use App\Http\Controllers\Products\ProductController;
use App\Http\Controllers\WareHouse\WareHouseMaterialsController;
use App\Http\Controllers\Role\SuperAdminController;
use App\Http\Controllers\Role\DirectorController;
use App\Http\Controllers\CounterAgencyController;
use App\Http\Controllers\Role\AdminController;
use App\Http\Controllers\WareHouseController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;   


Route::prefix('/user')->group(function () {

    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/register', [LoginController::class, 'register']);
    Route::post('/logout', [LoginController::class, 'logout']);
    
    Route::get('/all', [UserController::class, 'index']);

    // Route::apiResource('materials',MaterialController::class);
    // Route::apiResource('products', ProductController::class);
    // Route::apiResource('counter_agencies',CounterAgencyController::class);
    // Route::apiResource('ware_houses',WareHouseController::class);
    // Route::apiResource('buy_material', BuyMaterialController::class);

    // Route::get('getItem/{id}', [BuyMaterialController::class, 'getItem']);
    // Route::get('getItemhistory/{id}', [BuyMaterialController::class, 'getItemhistory']);
    // Route::post('buy_material/send', [BuyMaterialController::class, 'send']);

});

Route::apiResource('materials',MaterialController::class);
Route::apiResource('products', ProductController::class);
Route::apiResource('counter_agencies',CounterAgencyController::class);
Route::apiResource('ware_houses',WareHouseController::class);
Route::apiResource('buy_material', BuyMaterialController::class);
Route::apiResource('warehouse_materials', WareHouseMaterialsController::class);

Route::get('getItem/{id}', [BuyMaterialController::class, 'getItem']);
Route::get('getItemhistory/{id}', [BuyMaterialController::class, 'getItemhistory']);
Route::get('ware_houses', [WareHouseController::class, 'index']);
Route::get('productMaterials', [ProductController::class, 'productMaterials']);

Route::post('buy_material/send', [BuyMaterialController::class, 'send']);
Route::post('sales/report', [ProductController::class, 'report']);
Route::post('sales/sale', [ProductController::class, 'sale']);

Route::post('sale', [ProductController::class, 'sale']);

Route::get('calculateMaterials/{product_id}', [ProductController::class, 'calculateMaterials']);










// Route::group(['prefix'=>'/super-admin'], function () {
//     Route::get('/',[SuperAdminController::class,'index']);
// });


// Route::group(['prefix'=>'/admin'], function () {
//     Route::get('/',[AdminController::class,'index']);
// });


// Route::group(['prefix'=>'/director'], function () {
//     Route::get('/',[DirectorController::class,'index']);
// });






