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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('exam_type_id');
            $table->string('student_code')->unique();
            $table->string('firstname');
            $table->string('surname');
            $table->string('othername')->nullable();
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female']);
            $table->string('state_of_origin');
            $table->string('lga');
            $table->string('passport')->nullable();

            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools');
            $table->foreign('exam_type_id')->references('id')->on('exam_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
