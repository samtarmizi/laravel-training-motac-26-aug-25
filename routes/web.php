<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\BulkUploadInventoryController;
use App\Http\Controllers\DeletedInventoryController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\APIPostController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\ChatController;
use Cloudstudio\Ollama\Facades\Ollama;
use App\Http\Controllers\FileController;

use Illuminate\Support\Facades\Http;

Route::redirect('/', '/home');

Route::view('/tema', 'admin.layouts.main');

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

Route::get('/audits', [AuditController::class, 'index'])->name('audits.index');

// Chat routes
Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
Route::post('/chat/send', [ChatController::class, 'sendMessage'])->name('chat.send');
Route::post('/chat/stream', [ChatController::class, 'sendMessageStream'])->name('chat.stream');
Route::get('/chat/models', [ChatController::class, 'getModels'])->name('chat.models');

// File upload and RAG routes
Route::get('/files', [FileController::class, 'index'])->name('files.index');
Route::get('/files/list', [FileController::class, 'list'])->name('files.list');
Route::post('/files/upload', [FileController::class, 'upload'])->name('files.upload');
Route::post('/files/search', [FileController::class, 'search'])->name('files.search');
Route::delete('/files/{id}', [FileController::class, 'destroy'])->name('files.destroy');
Route::post('/files/{id}/process', [FileController::class, 'process'])->name('files.process');

Route::get('ask-ollama', function () {
    // $response = Ollama::agent('You are an expert PHP developer.')
    // ->prompt('Create a Laravel middleware that logs API requests with rate limiting')
    // ->model('gemma3:1b')
    // ->options(['temperature' => 0.2]) // Less creative for code
    // ->ask();

    $response = Ollama::agent('You are a helpful assistant.')
    ->prompt('Explain quantum computing in simple terms')
    ->model('gemma3:1b')
    ->ask();

    return $response;
})->name('ask-ollama');

Route::get('chat-ollama', function () {
    $response = Http::post('http://127.0.0.1:11434/api/generate', [
        'model' => 'gemma3:1b',
        'prompt' => 
        // [
            'hello, can you help me with a laravel question?',
        // ],
        // 'temperature' => 0.2,
        // 'max_tokens' => 1000,
        'stream' => false,
    ]);

    return $response;
})->name('chat-ollama');