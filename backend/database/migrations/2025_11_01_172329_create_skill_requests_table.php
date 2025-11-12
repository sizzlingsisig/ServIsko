<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skill_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('set null');
            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();
            $table->text('admin_notes')->nullable();
            $table->softDeletes();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            // Prevent duplicate pending requests for same skill from same user
            $table->unique(['user_id', 'name', 'status'], 'unique_pending_skill_request');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skill_requests');
    }
};
