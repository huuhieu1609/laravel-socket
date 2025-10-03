<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class KhachHang extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'khach_hangs';
    protected $fillable = [
        'ho_va_ten',
        'email',
        'password',
        'fcm_token'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Relationship với messages (tin nhắn đã gửi)
    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    // Relationship với rooms thông qua participants
    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'room_participants', 'user_id', 'room_id')
                    ->withPivot('role', 'joined_at', 'left_at')
                    ->withTimestamps();
    }

    // Relationship với room participants
    public function roomParticipants()
    {
        return $this->hasMany(RoomParticipant::class, 'user_id');
    }

    // Lấy các phòng đang tham gia
    public function activeRooms()
    {
        return $this->rooms()->wherePivotNull('left_at');
    }

    // Lấy tin nhắn chưa đọc
    public function unreadMessages()
    {
        return $this->hasManyThrough(Message::class, RoomParticipant::class, 'user_id', 'room_id', 'id', 'room_id')
                    ->where('is_read', false)
                    ->where('sender_id', '!=', $this->id);
    }
}
