<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VideoCall extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'initiator_id',
        'call_id',
        'type',
        'status',
        'started_at',
        'ended_at',
        'duration'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime'
    ];

    // Relationship với room
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    // Relationship với initiator
    public function initiator()
    {
        return $this->belongsTo(KhachHang::class, 'initiator_id');
    }

    // Relationship với participants
    public function participants()
    {
        return $this->hasMany(VideoCallParticipant::class);
    }

    // Relationship với users thông qua participants
    public function users()
    {
        return $this->belongsToMany(KhachHang::class, 'video_call_participants', 'video_call_id', 'user_id')
                    ->withPivot('status', 'joined_at', 'left_at', 'is_muted', 'is_video_off')
                    ->withTimestamps();
    }

    // Boot method để tự động tạo call_id
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($videoCall) {
            if (empty($videoCall->call_id)) {
                $videoCall->call_id = Str::uuid();
            }
        });
    }

    // Scope để lấy cuộc gọi đang hoạt động
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Scope để lấy cuộc gọi pending
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Method để bắt đầu cuộc gọi
    public function start()
    {
        $this->update([
            'status' => 'active',
            'started_at' => now()
        ]);
    }

    // Method để kết thúc cuộc gọi
    public function end()
    {
        $duration = $this->started_at ? now()->diffInSeconds($this->started_at) : 0;

        $this->update([
            'status' => 'ended',
            'ended_at' => now(),
            'duration' => $duration
        ]);
    }

    // Method để đánh dấu cuộc gọi nhỡ
    public function markAsMissed()
    {
        $this->update(['status' => 'missed']);
    }

    // Method để kiểm tra xem cuộc gọi có đang hoạt động không
    public function isActive()
    {
        return $this->status === 'active';
    }

    // Method để lấy số người tham gia
    public function getParticipantsCount()
    {
        return $this->participants()->where('status', 'joined')->count();
    }
}
