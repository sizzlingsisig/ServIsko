<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_profile_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_profile_id')
                ->constrained('provider_profiles')
                ->onDeleteCascade();
            $table->foreignId('skill_id')
                ->constrained('skills')
                ->onDeleteCascade();
            $table->unique(['provider_profile_id', 'skill_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_profile_skills');
    }
};
