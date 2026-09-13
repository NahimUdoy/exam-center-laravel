<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('english_drills', function (Blueprint $table) {
            $table->text('sentence')->nullable()->after('prompt');
        });
    }

    public function down(): void
    {
        Schema::table('english_drills', function (Blueprint $table) {
            $table->dropColumn('sentence');
        });
    }
};
