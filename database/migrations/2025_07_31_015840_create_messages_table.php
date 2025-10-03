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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->onDelete('cascade'); // ID phòng chat
            $table->foreignId('sender_id')->constrained('khach_hangs')->onDelete('cascade'); // ID người gửi
            $table->text('content'); // Nội dung tin nhắn
            $table->enum('type', ['text', 'image', 'file'])->default('text'); // Loại tin nhắn
            $table->string('file_path')->nullable(); // Đường dẫn file nếu có
            $table->boolean('is_read')->default(false); // Trạng thái đã đọc
            $table->timestamp('read_at')->nullable(); // Thời gian đọc
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
