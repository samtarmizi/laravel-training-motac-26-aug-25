<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\BulkUploadInventoryController;
use App\Http\Controllers\DeletedInventoryController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\APIPostController;

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

Route::get('inventories-deleted', [DeletedInventoryController::class, 'index'])->name('inventories.deleted.index');
Route::get('/inventories-deleted/{inventory}/restore', [DeletedInventoryController::class, 'restore'])->name('inventories.deleted.restore');
Route::get('/inventories-deleted/{inventory}/force-delete', [DeletedInventoryController::class, 'forceDelete'])->name('inventories.deleted.force-delete');

Route::get('/applications/create', [ApplicationController::class, 'create'])->name('applications.create');
// api for query inventories by user_id
Route::get('/inventories-by-user/{user_id}', [ApplicationController::class, 'getInventoriesByUser']);

Route::get('posts', [APIPostController::class, 'index'])->name('posts.index');
