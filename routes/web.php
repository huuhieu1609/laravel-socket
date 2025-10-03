<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatViewController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/chat', [ChatViewController::class, 'index'])->name('chat');
Route::get('/video-call', function () {
    return view('video-call');
})->name('video-call');
