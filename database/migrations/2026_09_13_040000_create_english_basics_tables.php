<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('english_lessons', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->char('letter', 1)->index();
            $table->string('category')->index();
            $table->string('title');
            $table->string('subtitle');
            $table->string('level')->default('A1');
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('summary');
            $table->text('body');
            $table->json('rules');
            $table->json('examples');
            $table->timestamps();
        });

        Schema::create('english_drills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('english_lesson_id')->constrained()->cascadeOnDelete();
            $table->string('kind')->default('mcq');
            $table->text('prompt');
            $table->json('options');
            $table->unsignedTinyInteger('correct');
            $table->text('explanation')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('english_drills');
        Schema::dropIfExists('english_lessons');
    }
};
