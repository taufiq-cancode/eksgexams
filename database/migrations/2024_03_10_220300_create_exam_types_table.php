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
        Schema::create('exam_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->timestamps();
        });

        $exam_types = [
            ['name' => 'CE', 'description' => 'Common Entrance Exams'],
            ['name' => 'JSS3', 'description' => 'Junior Secondary School 3'],
            ['name' => 'SS2', 'description' => 'Senior Secondary School 3']
        ];

        foreach ($exam_types as $exam_type) {
            DB::table('exam_types')->insert([
                'name' => $exam_type['name'],
                'description' => $exam_type['description']
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_types');
    }
};
