<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    protected $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        $this->pushService = $pushService;
    }

    /**
     * Lấy danh sách notifications của user
     */
    public function index(Request $request)
    {
        $user = $request->user;

        $notifications = Notification::where('user_id', $user->id)
                                   ->orderBy('created_at', 'desc')
                                   ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Lấy số notifications chưa đọc
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user;

        $count = Notification::where('user_id', $user->id)
                            ->where('is_read', false)
                            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $count
            ]
        ]);
    }

    /**
     * Đánh dấu notification đã đọc
     */
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user;

        $notification = Notification::where('id', $id)
                                  ->where('user_id', $user->id)
                                  ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification không tồn tại'
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Đã đánh dấu đọc'
        ]);
    }

    /**
     * Đánh dấu tất cả notifications đã đọc
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user;

        Notification::where('user_id', $user->id)
                   ->where('is_read', false)
                   ->update([
                       'is_read' => true,
                       'read_at' => now()
                   ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã đánh dấu tất cả đã đọc'
        ]);
    }

    /**
     * Xóa notification
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user;

        $notification = Notification::where('id', $id)
                                  ->where('user_id', $user->id)
                                  ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification không tồn tại'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa notification'
        ]);
    }

    /**
     * Cập nhật FCM token
     */
    public function updateFCMToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user;

        $this->pushService->updateFCMToken($user->id, $request->fcm_token);

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật FCM token'
        ]);
    }

    /**
     * Xóa FCM token
     */
    public function removeFCMToken(Request $request)
    {
        $user = $request->user;

        $this->pushService->removeFCMToken($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa FCM token'
        ]);
    }

    /**
     * Test push notification
     */
    public function testNotification(Request $request)
    {
        $user = $request->user;

        $this->pushService->sendToUser(
            $user->id,
            'Test Notification',
            'Đây là thông báo test từ server',
            [
                'type' => 'test',
                'action' => 'test_action'
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Đã gửi test notification'
        ]);
    }
}
