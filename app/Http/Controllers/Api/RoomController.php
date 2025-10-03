<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomParticipant;
use App\Models\KhachHang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user;
        $rooms = $user->activeRooms()->with(['lastMessage.sender', 'participants.user'])->get();

        return response()->json([
            'success' => true,
            'data' => $rooms
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:private,group',
            'participant_ids' => 'required|array|min:1',
            'participant_ids.*' => 'exists:khach_hangs,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user;

        // Tạo phòng mới
        $room = Room::create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
        ]);

        // Thêm người tạo phòng làm admin
        RoomParticipant::create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'role' => 'admin',
        ]);

        // Thêm các thành viên khác
        foreach ($request->participant_ids as $participantId) {
            if ($participantId != $user->id) {
                RoomParticipant::create([
                    'room_id' => $room->id,
                    'user_id' => $participantId,
                    'role' => 'member',
                ]);
            }
        }

        $room->load(['participants.user', 'lastMessage.sender']);

        return response()->json([
            'success' => true,
            'message' => 'Tạo phòng thành công',
            'data' => $room
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user;
        $room = Room::with(['participants.user', 'messages.sender'])
                    ->whereHas('participants', function ($query) use ($user) {
                        $query->where('user_id', $user->id)->whereNull('left_at');
                    })
                    ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $room
        ]);
    }

    public function addParticipant(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:khach_hangs,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user;
        $room = Room::findOrFail($id);

        // Kiểm tra quyền admin
        $participant = RoomParticipant::where('room_id', $room->id)
                                    ->where('user_id', $user->id)
                                    ->where('role', 'admin')
                                    ->first();

        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền thêm thành viên'
            ], 403);
        }

        // Kiểm tra user đã tham gia chưa
        $existingParticipant = RoomParticipant::where('room_id', $room->id)
                                            ->where('user_id', $request->user_id)
                                            ->first();

        if ($existingParticipant) {
            if ($existingParticipant->left_at) {
                // Nếu đã rời phòng thì cho tham gia lại
                $existingParticipant->update(['left_at' => null]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Người dùng đã tham gia phòng này'
                ], 400);
            }
        } else {
            // Thêm thành viên mới
            RoomParticipant::create([
                'room_id' => $room->id,
                'user_id' => $request->user_id,
                'role' => 'member',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Thêm thành viên thành công'
        ]);
    }

    public function leaveRoom(Request $request, $id)
    {
        $user = $request->user;
        $participant = RoomParticipant::where('room_id', $id)
                                    ->where('user_id', $user->id)
                                    ->whereNull('left_at')
                                    ->firstOrFail();

        $participant->update(['left_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Rời phòng thành công'
        ]);
    }
}
