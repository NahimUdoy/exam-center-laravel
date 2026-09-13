<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->string('cover_image')->nullable()->after('free_practice');
            $table->string('icon')->default('GraduationCap')->after('cover_image');
        });

        Schema::table('practice_questions', function (Blueprint $table) {
            $table->string('image_url')->nullable()->after('passage');
            $table->json('exam_slugs')->nullable()->after('image_url');
            $table->boolean('practiced')->default(false)->after('exam_slugs');
            $table->boolean('bookmarked')->default(false)->after('practiced');
        });

        Schema::table('mock_tests', function (Blueprint $table) {
            $table->string('cover_image')->nullable()->after('runner');
        });

        Schema::table('pte_questions', function (Blueprint $table) {
            $table->string('image_url')->nullable()->after('payload');
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->string('cover_image')->nullable()->after('meta');
            $table->string('badge_image')->nullable()->after('cover_image');
        });

        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('label');
            $table->string('color');
            $table->string('soft');
            $table->string('icon')->default('Sparkles');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('tag')->nullable();
            $table->string('color');
            $table->string('soft');
            $table->string('glow')->nullable();
            $table->string('total')->nullable();
            $table->string('cover_image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('course_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('count_label');
            $table->unsignedInteger('value')->default(0);
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('mock_sections', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('label');
            $table->string('color');
            $table->string('soft');
            $table->string('band')->nullable();
            $table->timestamps();
        });

        Schema::create('certificate_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('label');
            $table->string('hint')->nullable();
            $table->timestamps();
        });

        Schema::create('pte_task_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('label');
            $table->string('short')->nullable();
            $table->string('color');
            $table->timestamps();
        });

        Schema::create('media_items', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique();
            $table->string('url');
            $table->string('name');
            $table->string('alt')->nullable();
            $table->unsignedInteger('size')->default(0);
            $table->string('mime')->nullable();
            $table->timestamps();
        });

        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->json('payload');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('media_items');
        Schema::dropIfExists('pte_task_types');
        Schema::dropIfExists('certificate_categories');
        Schema::dropIfExists('mock_sections');
        Schema::dropIfExists('course_modules');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('skills');

        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn(['cover_image', 'badge_image']);
        });
        Schema::table('pte_questions', function (Blueprint $table) {
            $table->dropColumn('image_url');
        });
        Schema::table('mock_tests', function (Blueprint $table) {
            $table->dropColumn('cover_image');
        });
        Schema::table('practice_questions', function (Blueprint $table) {
            $table->dropColumn(['image_url', 'exam_slugs', 'practiced', 'bookmarked']);
        });
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['cover_image', 'icon']);
        });
    }
};
