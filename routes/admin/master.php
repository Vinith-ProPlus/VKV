<?php

use App\Http\Controllers\Admin\Master\ContractTypeController;
use App\Http\Controllers\Admin\Master\ProductController;
use App\Http\Controllers\Admin\Master\UnitOfMeasurementController;
use App\Http\Controllers\Admin\Master\WarehouseController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Master\CityController;
use App\Http\Controllers\Admin\Master\DistrictController;
use App\Http\Controllers\Admin\Master\PincodeController;
use App\Http\Controllers\Admin\Master\StatesController;
use App\Http\Controllers\Admin\Master\ProductCategoryController;
use App\Http\Controllers\Admin\Master\TaxController;


Route::resource('states', StatesController::class);
Route::put('states/restore/{id}', [StatesController::class, 'restore'])->name('states.restore')->middleware('can:Restore States');

Route::resource('districts', DistrictController::class);
Route::put('districts/restore/{id}', [DistrictController::class, 'restore'])->name('districts.restore')->middleware('can:Restore Districts');

Route::resource('pincodes', PincodeController::class);
Route::put('pincodes/restore/{id}', [PincodeController::class, 'restore'])->name('pincodes.restore')->middleware('can:Restore Pincodes');

Route::resource('cities', CityController::class);
Route::put('cities/restore/{id}', [CityController::class, 'restore'])->name('cities.restore')->middleware('can:Restore Pincodes');

Route::resource('product_categories', ProductCategoryController::class);
Route::put('product_categories/restore/{id}', [ProductCategoryController::class, 'restore'])->name('product_categories.restore')->middleware('can:Restore Product Category');

Route::resource('taxes', TaxController::class)->except(['show']);
Route::put('taxes/restore/{id}', [TaxController::class, 'restore'])->name('taxes.restore')->middleware('can:Restore Tax');

Route::resource('warehouses', WarehouseController::class);
Route::put('warehouses/restore/{id}', [WarehouseController::class, 'restore'])->name('warehouses.restore')->middleware('can:Restore Warehouse');

Route::resource('contract_types', ContractTypeController::class);
Route::put('contract_types/restore/{id}', [ContractTypeController::class, 'restore'])->name('contract_types.restore')->middleware('can:Restore Contract Type');

Route::resource('units', UnitOfMeasurementController::class)->except(['show']);
Route::put('units/restore/{id}', [UnitOfMeasurementController::class, 'restore'])->name('units.restore')->middleware('can:Restore Unit of Measurement');

Route::resource('products', ProductController::class)->except(['show']);
Route::put('products/restore/{id}', [ProductController::class, 'restore'])->name('products.restore')->middleware('can:Restore Product');

