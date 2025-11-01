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
    Schema::create('listings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('seeker_user_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('category_id')->nullable()->constrained('categories')->onDeleteCascade();
        $table->string('title');
        $table->text('description')->nullable();
        $table->decimal('budget', 10, 2)->nullable();
        $table->enum('status', ['active', 'closed', 'expired'])->default('active');
        $table->timestamps();
        $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
