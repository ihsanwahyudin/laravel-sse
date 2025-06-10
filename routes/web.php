<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ServerSentEventController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
Route::get('/chat/stream', [ChatController::class, 'stream'])->name('chat.stream');
Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send');
Route::get('/ping', [ServerSentEventController::class, 'ping'])->name('sse.ping');
Route::get('/sse/chat-counter', [ServerSentEventController::class, 'chatCounter'])->name('sse.chat-counter');