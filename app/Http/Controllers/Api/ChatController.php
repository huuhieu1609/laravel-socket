<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Room;
use App\Models\RoomParticipant;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    protected $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        $this->pushService = $pushService;
    }
    public function getMessages(Request $request, $roomId)
    {
        $user = $request->user;

        // Kiểm tra user có tham gia phòng không
        $participant = RoomParticipant::where('room_id', $roomId)
                                    ->where('user_id', $user->id)
                                    ->whereNull('left_at')
                                    ->first();

        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không tham gia phòng này'
            ], 403);
        }

        $messages = Message::with('sender')
                          ->where('room_id', $roomId)
                          ->orderBy('created_at', 'desc')
                          ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    public function sendMessage(Request $request, $roomId)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:1000',
            'type' => 'in:text,image,file',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user;

        // Kiểm tra user có tham gia phòng không
        $participant = RoomParticipant::where('room_id', $roomId)
                                    ->where('user_id', $user->id)
                                    ->whereNull('left_at')
                                    ->first();

        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không tham gia phòng này'
            ], 403);
        }

        $message = Message::create([
            'room_id' => $roomId,
            'sender_id' => $user->id,
            'content' => $request->content,
            'type' => $request->type ?? 'text',
            'file_path' => $request->file_path ?? null,
        ]);

        $message->load('sender');

        // Gửi push notification cho tất cả users trong hệ thống
        $room = Room::find($roomId);
        $this->pushService->sendMessageNotification($user, $room, $message);

        // Gửi notification cho tất cả users khác (kể cả không trong phòng)
        $this->sendGlobalMessageNotification($user, $room, $message);

        return response()->json([
            'success' => true,
            'message' => 'Gửi tin nhắn thành công',
            'data' => $message
        ], 201);
    }

    /**
     * Gửi notification cho tất cả users khác
     */
    private function sendGlobalMessageNotification($sender, $room, $message)
    {
        // Lấy tất cả users trừ người gửi
        $allUsers = \App\Models\KhachHang::where('id', '!=', $sender->id)->get();

        foreach ($allUsers as $user) {
            // Kiểm tra user có trong phòng không
            $isInRoom = $room->participants()
                            ->where('user_id', $user->id)
                            ->whereNull('left_at')
                            ->exists();

            // Gửi notification cho tất cả users (kể cả không trong phòng)
            $this->pushService->sendToUser(
                $user->id,
                $sender->ho_va_ten . ' (@' . $room->name . ')',
                $message->content,
                [
                    'type' => 'global_message',
                    'room_id' => $room->id,
                    'room_name' => $room->name,
                    'sender_id' => $sender->id,
                    'sender_name' => $sender->ho_va_ten,
                    'message_id' => $message->id,
                    'is_in_room' => $isInRoom,
                    'action' => 'open_room'
                ]
            );
        }
    }

    public function markAsRead(Request $request, $roomId)
    {
        $user = $request->user;

        // Kiểm tra user có tham gia phòng không
        $participant = RoomParticipant::where('room_id', $roomId)
                                    ->where('user_id', $user->id)
                                    ->whereNull('left_at')
                                    ->first();

        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không tham gia phòng này'
            ], 403);
        }

        // Đánh dấu tất cả tin nhắn trong phòng là đã đọc
        Message::where('room_id', $roomId)
               ->where('sender_id', '!=', $user->id)
               ->where('is_read', false)
               ->update([
                   'is_read' => true,
                   'read_at' => now()
               ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã đánh dấu đọc'
        ]);
    }

    public function getUnreadCount(Request $request)
    {
        $user = $request->user;
        $unreadCounts = [];

        // Lấy số tin nhắn chưa đọc cho mỗi phòng
        $rooms = $user->activeRooms();

        foreach ($rooms as $room) {
            $count = Message::where('room_id', $room->id)
                           ->where('sender_id', '!=', $user->id)
                           ->where('is_read', false)
                           ->count();

            $unreadCounts[$room->id] = $count;
        }

        return response()->json([
            'success' => true,
            'data' => $unreadCounts
        ]);
    }

    public function deleteMessage(Request $request, $messageId)
    {
        $user = $request->user;
        $message = Message::findOrFail($messageId);

        // Chỉ cho phép xóa tin nhắn của chính mình
        if ($message->sender_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chỉ có thể xóa tin nhắn của mình'
            ], 403);
        }

        $message->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa tin nhắn thành công'
        ]);
    }

    /**
     * Lấy tin nhắn mới nhất từ tất cả phòng
     */
    public function getLatestMessages(Request $request)
    {
        $user = $request->user;

        // Lấy tin nhắn mới nhất từ tất cả phòng mà user có thể thấy
        $latestMessages = Message::with(['sender', 'room'])
                                ->whereHas('room.participants', function($query) use ($user) {
                                    $query->where('user_id', $user->id);
                                })
                                ->orWhereHas('room', function($query) {
                                    $query->where('type', 'public');
                                })
                                ->orderBy('created_at', 'desc')
                                ->limit(10)
                                ->get()
                                ->groupBy('room_id')
                                ->map(function($messages) {
                                    return $messages->first();
                                })
                                ->values();

        return response()->json([
            'success' => true,
            'data' => $latestMessages
        ]);
    }
}
