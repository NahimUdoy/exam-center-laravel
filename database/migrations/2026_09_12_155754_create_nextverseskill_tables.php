<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('short_name');
            $table->string('tagline');
            $table->string('accent');
            $table->string('accent_soft');
            $table->text('description');
            $table->unsignedInteger('question_count')->default(0);
            $table->boolean('free_practice')->default(true);
            $table->timestamps();
        });

        Schema::create('task_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('skill');
            $table->string('name');
            $table->unsignedInteger('done')->default(0);
            $table->unsignedInteger('total')->default(0);
            $table->json('exam_slugs');
            $table->timestamps();
        });

        Schema::create('practice_questions', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->nullable();
            $table->foreignId('task_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('skill')->nullable();
            $table->string('task_slug')->nullable();
            $table->string('title');
            $table->string('difficulty')->default('Medium');
            $table->unsignedInteger('appeared')->default(0);
            $table->boolean('is_prediction')->default(false);
            $table->text('prompt')->nullable();
            $table->text('passage')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('mock_tests', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('exam_slug');
            $table->string('section');
            $table->string('title');
            $table->string('subtitle');
            $table->unsignedInteger('duration_min');
            $table->unsignedInteger('questions')->default(0);
            $table->unsignedInteger('attempts')->default(0);
            $table->string('difficulty')->default('Standard');
            $table->boolean('featured')->default(false);
            $table->string('runner')->default('generic'); // ielts-reading | pte | generic
            $table->timestamps();
        });

        Schema::create('mock_passages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mock_test_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->text('text');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('mock_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mock_test_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('number');
            $table->unsignedTinyInteger('part')->default(1);
            $table->text('prompt');
            $table->json('options');
            $table->unsignedTinyInteger('correct_index');
            $table->string('skill_tag')->nullable();
            $table->text('tip')->nullable();
            $table->timestamps();
        });

        Schema::create('pte_questions', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique();
            $table->string('type');
            $table->string('section');
            $table->string('title');
            $table->text('instructions');
            $table->json('payload')->nullable();
            $table->string('correct_text')->nullable();
            $table->json('answer_key')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('exam_slug');
            $table->string('category');
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('locked');
            $table->string('score_label')->nullable();
            $table->string('score_value')->nullable();
            $table->string('issued_on')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('diagnostic_questions', function (Blueprint $table) {
            $table->id();
            $table->string('exam_slug');
            $table->string('external_id');
            $table->text('prompt');
            $table->json('options');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('academy_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('exam_slug');
            $table->string('tier');
            $table->string('level_label');
            $table->json('plan_json');
            $table->timestamps();
        });

        Schema::create('mock_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('mock_test_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('score_pct')->default(0);
            $table->json('analysis_json')->nullable();
            $table->json('answers_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mock_attempts');
        Schema::dropIfExists('academy_plans');
        Schema::dropIfExists('diagnostic_questions');
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('pte_questions');
        Schema::dropIfExists('mock_questions');
        Schema::dropIfExists('mock_passages');
        Schema::dropIfExists('mock_tests');
        Schema::dropIfExists('practice_questions');
        Schema::dropIfExists('task_types');
        Schema::dropIfExists('exams');
    }
};
