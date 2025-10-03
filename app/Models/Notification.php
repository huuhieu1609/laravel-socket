<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'data',
        'fcm_token',
        'is_read',
        'read_at',
        'sent_at'
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'sent_at' => 'datetime'
    ];

    // Relationship với user
    public function user()
    {
        return $this->belongsTo(KhachHang::class, 'user_id');
    }

    // Scope để lấy thông báo chưa đọc
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    // Scope để lấy thông báo đã đọc
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    // Scope để lấy thông báo theo loại
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Method để đánh dấu đã đọc
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }

    // Method để đánh dấu đã gửi
    public function markAsSent()
    {
        $this->update(['sent_at' => now()]);
    }

    // Method để tạo thông báo tin nhắn mới
    public static function createMessageNotification($userId, $sender, $room, $message)
    {
        return self::create([
            'user_id' => $userId,
            'type' => 'message',
            'title' => $sender->ho_va_ten,
            'body' => $message->content,
            'data' => [
                'room_id' => $room->id,
                'room_name' => $room->name,
                'sender_id' => $sender->id,
                'sender_name' => $sender->ho_va_ten,
                'message_id' => $message->id,
                'message_content' => $message->content
            ]
        ]);
    }

    // Method để tạo thông báo cuộc gọi đến
    public static function createCallNotification($userId, $initiator, $room, $callType)
    {
        $typeText = $callType === 'video' ? 'video' : 'audio';

        return self::create([
            'user_id' => $userId,
            'type' => 'call',
            'title' => 'Cuộc gọi ' . $typeText . ' đến',
            'body' => $initiator->ho_va_ten . ' đang gọi bạn trong phòng ' . $room->name,
            'data' => [
                'room_id' => $room->id,
                'room_name' => $room->name,
                'initiator_id' => $initiator->id,
                'initiator_name' => $initiator->ho_va_ten,
                'call_type' => $callType
            ]
        ]);
    }

    // Method để tạo thông báo thành viên mới
    public static function createMemberNotification($userId, $newMember, $room)
    {
        return self::create([
            'user_id' => $userId,
            'type' => 'member',
            'title' => 'Thành viên mới',
            'body' => $newMember->ho_va_ten . ' đã tham gia phòng ' . $room->name,
            'data' => [
                'room_id' => $room->id,
                'room_name' => $room->name,
                'new_member_id' => $newMember->id,
                'new_member_name' => $newMember->ho_va_ten
            ]
        ]);
    }
}
