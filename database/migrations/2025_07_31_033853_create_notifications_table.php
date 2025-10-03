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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('khach_hangs')->onDelete('cascade'); // Người nhận thông báo
            $table->string('type'); // Loại thông báo: message, call, etc.
            $table->string('title'); // Tiêu đề thông báo
            $table->text('body'); // Nội dung thông báo
            $table->json('data')->nullable(); // Dữ liệu bổ sung (room_id, sender_id, etc.)
            $table->string('fcm_token')->nullable(); // FCM token cho push notification
            $table->boolean('is_read')->default(false); // Trạng thái đã đọc
            $table->timestamp('read_at')->nullable(); // Thời gian đọc
            $table->timestamp('sent_at')->nullable(); // Thời gian gửi
            $table->timestamps();

            // Index để tối ưu query
            $table->index(['user_id', 'is_read']);
            $table->index(['type', 'sent_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
