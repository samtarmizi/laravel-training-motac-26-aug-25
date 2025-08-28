<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\BulkUploadInventoryController;

Route::redirect('/', '/home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/inventories', [InventoryController::class, 'index'])->name('inventories.index');
Route::get('/inventories/create', [InventoryController::class, 'create'])->name('inventories.create');
Route::post('/inventories/create', [InventoryController::class, 'store'])->name('inventories.store');
Route::get('/inventories/{inventory}', [InventoryController::class, 'show'])->name('inventories.show');
Route::get('/inventories/{inventory}/edit', [InventoryController::class, 'edit'])->name('inventories.edit');
Route::post('/inventories/{inventory}/edit', [InventoryController::class, 'update'])->name('inventories.update');
Route::get('/inventories/{inventory}/destroy', [InventoryController::class, 'destroy'])->name('inventories.destroy');

Route::get('/bulk-upload-inventories', [BulkUploadInventoryController::class, 'create'])->name('inventories.bulk-upload.create');
Route::post('/bulk-upload-inventories', [BulkUploadInventoryController::class, 'store'])->name('inventories.bulk-upload.store');