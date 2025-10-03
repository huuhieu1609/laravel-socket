<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('video_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->onDelete('cascade'); // Phòng chat
            $table->foreignId('initiator_id')->constrained('khach_hangs')->onDelete('cascade'); // Người khởi tạo cuộc gọi
            $table->string('call_id')->unique(); // ID duy nhất cho cuộc gọi
            $table->enum('type', ['audio', 'video'])->default('video'); // Loại cuộc gọi
            $table->enum('status', ['pending', 'active', 'ended', 'missed'])->default('pending'); // Trạng thái cuộc gọi
            $table->timestamp('started_at')->nullable(); // Thời gian bắt đầu
            $table->timestamp('ended_at')->nullable(); // Thời gian kết thúc
            $table->integer('duration')->nullable(); // Thời lượng cuộc gọi (giây)
            $table->timestamps();
        });

        // Bảng participants trong cuộc gọi
        Schema::create('video_call_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_call_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained('khach_hangs')->onDelete('cascade');
            $table->enum('status', ['invited', 'joined', 'left', 'declined'])->default('invited');
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->boolean('is_muted')->default(false);
            $table->boolean('is_video_off')->default(false);
            $table->timestamps();

            // Đảm bảo mỗi user chỉ tham gia một lần trong mỗi cuộc gọi
            $table->unique(['video_call_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_call_participants');
        Schema::dropIfExists('video_calls');
    }
};
