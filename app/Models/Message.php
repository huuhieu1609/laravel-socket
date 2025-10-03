<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'sender_id',
        'content',
        'type',
        'file_path',
        'is_read',
        'read_at'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime'
    ];

    // Relationship với room
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    // Relationship với sender
    public function sender()
    {
        return $this->belongsTo(KhachHang::class, 'sender_id');
    }

    // Scope để lấy tin nhắn chưa đọc
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    // Scope để lấy tin nhắn đã đọc
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }
}
