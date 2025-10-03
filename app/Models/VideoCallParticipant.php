<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoCallParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'video_call_id',
        'user_id',
        'status',
        'joined_at',
        'left_at',
        'is_muted',
        'is_video_off'
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
        'is_muted' => 'boolean',
        'is_video_off' => 'boolean'
    ];

    // Relationship với video call
    public function videoCall()
    {
        return $this->belongsTo(VideoCall::class);
    }

    // Relationship với user
    public function user()
    {
        return $this->belongsTo(KhachHang::class, 'user_id');
    }

    // Scope để lấy participants đã tham gia
    public function scopeJoined($query)
    {
        return $query->where('status', 'joined');
    }

    // Scope để lấy participants đã rời
    public function scopeLeft($query)
    {
        return $query->where('status', 'left');
    }

    // Method để tham gia cuộc gọi
    public function join()
    {
        $this->update([
            'status' => 'joined',
            'joined_at' => now()
        ]);
    }

    // Method để rời cuộc gọi
    public function leave()
    {
        $this->update([
            'status' => 'left',
            'left_at' => now()
        ]);
    }

    // Method để từ chối cuộc gọi
    public function decline()
    {
        $this->update(['status' => 'declined']);
    }

    // Method để toggle mute
    public function toggleMute()
    {
        $this->update(['is_muted' => !$this->is_muted]);
    }

    // Method để toggle video
    public function toggleVideo()
    {
        $this->update(['is_video_off' => !$this->is_video_off]);
    }

    // Method để kiểm tra xem user có đang tham gia không
    public function isJoined()
    {
        return $this->status === 'joined';
    }

    // Method để kiểm tra xem user có bị mute không
    public function isMuted()
    {
        return $this->is_muted;
    }

    // Method để kiểm tra xem video có bị tắt không
    public function isVideoOff()
    {
        return $this->is_video_off;
    }
}
