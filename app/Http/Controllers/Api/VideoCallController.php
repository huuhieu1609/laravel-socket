<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VideoCall;
use App\Models\VideoCallParticipant;
use App\Models\Room;
use App\Models\RoomParticipant;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VideoCallController extends Controller
{
    protected $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        $this->pushService = $pushService;
    }
    public function initiate(Request $request, $roomId)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:audio,video',
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

        // Kiểm tra xem có cuộc gọi đang hoạt động trong phòng không
        $activeCall = VideoCall::where('room_id', $roomId)
                              ->where('status', 'active')
                              ->first();

        if ($activeCall) {
            return response()->json([
                'success' => false,
                'message' => 'Đã có cuộc gọi đang diễn ra trong phòng này'
            ], 400);
        }

        // Tạo cuộc gọi mới
        $videoCall = VideoCall::create([
            'room_id' => $roomId,
            'initiator_id' => $user->id,
            'type' => $request->type,
            'status' => 'pending'
        ]);

        // Thêm initiator vào participants
        VideoCallParticipant::create([
            'video_call_id' => $videoCall->id,
            'user_id' => $user->id,
            'status' => 'joined',
            'joined_at' => now()
        ]);

        // Thêm tất cả thành viên phòng vào participants
        $roomParticipants = RoomParticipant::where('room_id', $roomId)
                                          ->where('user_id', '!=', $user->id)
                                          ->whereNull('left_at')
                                          ->get();

        foreach ($roomParticipants as $participant) {
            VideoCallParticipant::create([
                'video_call_id' => $videoCall->id,
                'user_id' => $participant->user_id,
                'status' => 'invited'
            ]);
        }

        $videoCall->load(['participants.user', 'initiator']);

        // Gửi push notification cho cuộc gọi
        $this->pushService->sendCallNotification($user, Room::find($roomId), $request->type);

        return response()->json([
            'success' => true,
            'message' => 'Đã khởi tạo cuộc gọi',
            'data' => $videoCall
        ], 201);
    }

    public function join(Request $request, $callId)
    {
        $user = $request->user;

        $videoCall = VideoCall::where('call_id', $callId)->firstOrFail();

        // Kiểm tra user có được mời không
        $participant = VideoCallParticipant::where('video_call_id', $videoCall->id)
                                         ->where('user_id', $user->id)
                                         ->first();

        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không được mời tham gia cuộc gọi này'
            ], 403);
        }

        if ($participant->status === 'joined') {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã tham gia cuộc gọi này'
            ], 400);
        }

        // Tham gia cuộc gọi
        $participant->join();

        // Nếu cuộc gọi chưa active thì bắt đầu
        if ($videoCall->status === 'pending') {
            $videoCall->start();
        }

        $videoCall->load(['participants.user', 'initiator']);

        return response()->json([
            'success' => true,
            'message' => 'Đã tham gia cuộc gọi',
            'data' => $videoCall
        ]);
    }

    public function leave(Request $request, $callId)
    {
        $user = $request->user;

        $videoCall = VideoCall::where('call_id', $callId)->firstOrFail();

        $participant = VideoCallParticipant::where('video_call_id', $videoCall->id)
                                         ->where('user_id', $user->id)
                                         ->first();

        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không tham gia cuộc gọi này'
            ], 403);
        }

        // Rời cuộc gọi
        $participant->leave();

        // Kiểm tra xem còn ai trong cuộc gọi không
        $activeParticipants = VideoCallParticipant::where('video_call_id', $videoCall->id)
                                                 ->where('status', 'joined')
                                                 ->count();

        if ($activeParticipants === 0) {
            // Kết thúc cuộc gọi nếu không còn ai
            $videoCall->end();
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã rời cuộc gọi'
        ]);
    }

    public function decline(Request $request, $callId)
    {
        $user = $request->user;

        $videoCall = VideoCall::where('call_id', $callId)->firstOrFail();

        $participant = VideoCallParticipant::where('video_call_id', $videoCall->id)
                                         ->where('user_id', $user->id)
                                         ->first();

        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không được mời tham gia cuộc gọi này'
            ], 403);
        }

        // Từ chối cuộc gọi
        $participant->decline();

        return response()->json([
            'success' => true,
            'message' => 'Đã từ chối cuộc gọi'
        ]);
    }

    public function toggleMute(Request $request, $callId)
    {
        $user = $request->user;

        $videoCall = VideoCall::where('call_id', $callId)->firstOrFail();

        $participant = VideoCallParticipant::where('video_call_id', $videoCall->id)
                                         ->where('user_id', $user->id)
                                         ->where('status', 'joined')
                                         ->first();

        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không tham gia cuộc gọi này'
            ], 403);
        }

        $participant->toggleMute();

        return response()->json([
            'success' => true,
            'message' => $participant->is_muted ? 'Đã tắt mic' : 'Đã bật mic',
            'data' => [
                'is_muted' => $participant->is_muted
            ]
        ]);
    }

    public function toggleVideo(Request $request, $callId)
    {
        $user = $request->user;

        $videoCall = VideoCall::where('call_id', $callId)->firstOrFail();

        $participant = VideoCallParticipant::where('video_call_id', $videoCall->id)
                                         ->where('user_id', $user->id)
                                         ->where('status', 'joined')
                                         ->first();

        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không tham gia cuộc gọi này'
            ], 403);
        }

        $participant->toggleVideo();

        return response()->json([
            'success' => true,
            'message' => $participant->is_video_off ? 'Đã tắt camera' : 'Đã bật camera',
            'data' => [
                'is_video_off' => $participant->is_video_off
            ]
        ]);
    }

    public function getCallInfo(Request $request, $callId)
    {
        $videoCall = VideoCall::with(['participants.user', 'initiator'])
                             ->where('call_id', $callId)
                             ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $videoCall
        ]);
    }

    public function getActiveCall(Request $request, $roomId)
    {
        $videoCall = VideoCall::with(['participants.user', 'initiator'])
                             ->where('room_id', $roomId)
                             ->whereIn('status', ['pending', 'active'])
                             ->first();

        return response()->json([
            'success' => true,
            'data' => $videoCall
        ]);
    }
}
