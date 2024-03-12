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
        Schema::create('school_exam_type', function (Blueprint $table) {            
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('exam_type_id');

            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('exam_type_id')->references('id')->on('exam_types')->onDelete('cascade');

            $table->primary(['school_id', 'exam_type_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_exam_type');
    }
};
