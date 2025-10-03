<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\KhachHang;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    protected $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
    protected $fcmKey;

    public function __construct()
    {
        $this->fcmKey = config('services.fcm.server_key');
    }

    /**
     * Gửi push notification đến một user
     */
    public function sendToUser($userId, $title, $body, $data = [])
    {
        $user = KhachHang::find($userId);
        if (!$user) {
            return false;
        }

        // Lưu notification vào database
        $notification = Notification::create([
            'user_id' => $userId,
            'type' => $data['type'] ?? 'general',
            'title' => $title,
            'body' => $body,
            'data' => $data
        ]);

        // Gửi push notification nếu có FCM token
        if ($user->fcm_token) {
            $this->sendFCMNotification($user->fcm_token, $title, $body, $data);
            $notification->markAsSent();
        }

        return $notification;
    }

    /**
     * Gửi push notification đến nhiều users
     */
    public function sendToUsers($userIds, $title, $body, $data = [])
    {
        $users = KhachHang::whereIn('id', $userIds)->get();
        $tokens = $users->pluck('fcm_token')->filter()->toArray();

        // Lưu notifications vào database
        foreach ($userIds as $userId) {
            Notification::create([
                'user_id' => $userId,
                'type' => $data['type'] ?? 'general',
                'title' => $title,
                'body' => $body,
                'data' => $data
            ]);
        }

        // Gửi push notification
        if (!empty($tokens)) {
            $this->sendFCMNotificationToMultiple($tokens, $title, $body, $data);
        }

        return true;
    }

    /**
     * Gửi thông báo tin nhắn mới
     */
    public function sendMessageNotification($sender, $room, $message)
    {
        // Lấy tất cả thành viên phòng trừ người gửi
        $participants = $room->participants()
                            ->where('user_id', '!=', $sender->id)
                            ->whereNull('left_at')
                            ->get();

        $userIds = $participants->pluck('user_id')->toArray();

        if (empty($userIds)) {
            return false;
        }

        $title = $sender->ho_va_ten;
        $body = $message->content;
        $data = [
            'type' => 'message',
            'room_id' => $room->id,
            'room_name' => $room->name,
            'sender_id' => $sender->id,
            'sender_name' => $sender->ho_va_ten,
            'message_id' => $message->id,
            'action' => 'open_room'
        ];

        return $this->sendToUsers($userIds, $title, $body, $data);
    }

    /**
     * Gửi thông báo cuộc gọi đến
     */
    public function sendCallNotification($initiator, $room, $callType)
    {
        $participants = $room->participants()
                            ->where('user_id', '!=', $initiator->id)
                            ->whereNull('left_at')
                            ->get();

        $userIds = $participants->pluck('user_id')->toArray();

        if (empty($userIds)) {
            return false;
        }

        $typeText = $callType === 'video' ? 'video' : 'audio';
        $title = 'Cuộc gọi ' . $typeText . ' đến';
        $body = $initiator->ho_va_ten . ' đang gọi bạn trong phòng ' . $room->name;
        $data = [
            'type' => 'call',
            'room_id' => $room->id,
            'room_name' => $room->name,
            'initiator_id' => $initiator->id,
            'initiator_name' => $initiator->ho_va_ten,
            'call_type' => $callType,
            'action' => 'join_call'
        ];

        return $this->sendToUsers($userIds, $title, $body, $data);
    }

    /**
     * Gửi FCM notification đến một token
     */
    protected function sendFCMNotification($token, $title, $body, $data = [])
    {
        if (!$this->fcmKey) {
            Log::warning('FCM key not configured');
            return false;
        }

        $payload = [
            'to' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
                'badge' => 1
            ],
            'data' => $data,
            'priority' => 'high'
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->fcmKey,
                'Content-Type' => 'application/json'
            ])->post($this->fcmUrl, $payload);

            if ($response->successful()) {
                Log::info('FCM notification sent successfully', ['token' => $token]);
                return true;
            } else {
                Log::error('FCM notification failed', [
                    'token' => $token,
                    'response' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('FCM notification error', [
                'token' => $token,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Gửi FCM notification đến nhiều tokens
     */
    protected function sendFCMNotificationToMultiple($tokens, $title, $body, $data = [])
    {
        if (!$this->fcmKey) {
            Log::warning('FCM key not configured');
            return false;
        }

        // Chia tokens thành các nhóm 1000 (giới hạn của FCM)
        $tokenChunks = array_chunk($tokens, 1000);

        foreach ($tokenChunks as $tokenChunk) {
            $payload = [
                'registration_ids' => $tokenChunk,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                    'badge' => 1
                ],
                'data' => $data,
                'priority' => 'high'
            ];

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'key=' . $this->fcmKey,
                    'Content-Type' => 'application/json'
                ])->post($this->fcmUrl, $payload);

                if ($response->successful()) {
                    Log::info('FCM batch notification sent successfully', [
                        'count' => count($tokenChunk)
                    ]);
                } else {
                    Log::error('FCM batch notification failed', [
                        'response' => $response->body()
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('FCM batch notification error', [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Cập nhật FCM token cho user
     */
    public function updateFCMToken($userId, $token)
    {
        $user = KhachHang::find($userId);
        if ($user) {
            $user->update(['fcm_token' => $token]);
            return true;
        }
        return false;
    }

    /**
     * Xóa FCM token của user
     */
    public function removeFCMToken($userId)
    {
        $user = KhachHang::find($userId);
        if ($user) {
            $user->update(['fcm_token' => null]);
            return true;
        }
        return false;
    }
}
