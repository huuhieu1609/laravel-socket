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
        Schema::create('room_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->onDelete('cascade'); // ID phòng chat
            $table->foreignId('user_id')->constrained('khach_hangs')->onDelete('cascade'); // ID người dùng
            $table->enum('role', ['admin', 'member'])->default('member'); // Vai trò trong phòng
            $table->timestamp('joined_at')->useCurrent(); // Thời gian tham gia
            $table->timestamp('left_at')->nullable(); // Thời gian rời phòng
            $table->timestamps();

            // Đảm bảo mỗi user chỉ tham gia một lần trong mỗi room
            $table->unique(['room_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_participants');
    }
};
