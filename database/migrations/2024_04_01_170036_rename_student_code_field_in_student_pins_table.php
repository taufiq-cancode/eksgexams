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
        Schema::table('student_pins', function (Blueprint $table) {
            DB::statement('ALTER TABLE student_pins CHANGE student_code surname VARCHAR(255)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_pins', function (Blueprint $table) {
            DB::statement('ALTER TABLE student_pins CHANGE surname student_code VARCHAR(255)');
        });
    }
};
