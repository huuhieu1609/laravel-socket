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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên phòng chat
            $table->text('description')->nullable(); // Mô tả phòng
            $table->enum('type', ['private', 'group'])->default('private'); // Loại phòng: private hoặc group
            $table->string('avatar')->nullable(); // Avatar phòng
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
