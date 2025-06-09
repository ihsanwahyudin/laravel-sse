<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
Route::get('/chat/stream', [ChatController::class, 'stream'])->name('chat.stream');
Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send');
