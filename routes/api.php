<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\VideoCallController;
use App\Http\Controllers\Api\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('api.auth')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Rooms
    Route::get('/rooms', [RoomController::class, 'index']);
    Route::post('/rooms', [RoomController::class, 'store']);
    Route::get('/rooms/{id}', [RoomController::class, 'show']);
    Route::post('/rooms/{id}/participants', [RoomController::class, 'addParticipant']);
    Route::post('/rooms/{id}/leave', [RoomController::class, 'leaveRoom']);

    // Chat
    Route::get('/rooms/{roomId}/messages', [ChatController::class, 'getMessages']);
    Route::post('/rooms/{roomId}/messages', [ChatController::class, 'sendMessage']);
    Route::post('/rooms/{roomId}/read', [ChatController::class, 'markAsRead']);
    Route::get('/unread-count', [ChatController::class, 'getUnreadCount']);
    Route::delete('/messages/{messageId}', [ChatController::class, 'deleteMessage']);
    Route::get('/messages/latest', [ChatController::class, 'getLatestMessages']);

    // Video Call
    Route::post('/rooms/{roomId}/video-call/initiate', [VideoCallController::class, 'initiate']);
    Route::post('/video-call/{callId}/join', [VideoCallController::class, 'join']);
    Route::post('/video-call/{callId}/leave', [VideoCallController::class, 'leave']);
    Route::post('/video-call/{callId}/decline', [VideoCallController::class, 'decline']);
    Route::post('/video-call/{callId}/toggle-mute', [VideoCallController::class, 'toggleMute']);
    Route::post('/video-call/{callId}/toggle-video', [VideoCallController::class, 'toggleVideo']);
    Route::get('/video-call/{callId}', [VideoCallController::class, 'getCallInfo']);
    Route::get('/rooms/{roomId}/video-call/active', [VideoCallController::class, 'getActiveCall']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    Route::post('/notifications/fcm-token', [NotificationController::class, 'updateFCMToken']);
    Route::delete('/notifications/fcm-token', [NotificationController::class, 'removeFCMToken']);
    Route::post('/notifications/test', [NotificationController::class, 'testNotification']);
});
