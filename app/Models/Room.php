<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'avatar'
    ];

    // Relationship với messages
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    // Relationship với participants
    public function participants()
    {
        return $this->hasMany(RoomParticipant::class);
    }

    // Relationship với users thông qua participants
    public function users()
    {
        return $this->belongsToMany(KhachHang::class, 'room_participants', 'room_id', 'user_id')
                    ->withPivot('role', 'joined_at', 'left_at')
                    ->withTimestamps();
    }

    // Lấy tin nhắn cuối cùng
    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latest();
    }
}
