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
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
        $table->foreignId('service_id')->nullable()->constrained()->onDelete('set null');
        $table->string('title');
        $table->text('description')->nullable();
        $table->enum('post_type', ['offer', 'request']);
        $table->decimal('budget', 10, 2)->nullable();
        $table->enum('status', ['active', 'closed', 'expired'])->default('active');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
