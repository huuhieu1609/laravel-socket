<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'user_id',
        'role',
        'joined_at',
        'left_at'
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime'
    ];

    // Relationship với room
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    // Relationship với user
    public function user()
    {
        return $this->belongsTo(KhachHang::class, 'user_id');
    }

    // Scope để lấy participants đang hoạt động
    public function scopeActive($query)
    {
        return $query->whereNull('left_at');
    }

    // Scope để lấy admins
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }
}
