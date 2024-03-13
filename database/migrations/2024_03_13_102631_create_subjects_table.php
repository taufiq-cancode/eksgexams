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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $subjects = [
            ['name' => 'English Language'],
            ['name' => 'Mathematics'],
            ['name' => 'Biology'],
            ['name' => 'Chemistry'],
            ['name' => 'Civic Education'],
            ['name' => 'Physics'],
            ['name' => 'Agricultural Science'],
            ['name' => 'Further Mathematics'],
            ['name' => 'Computer Science'],
            ['name' => 'Accounting'],
            ['name' => 'Commerce'],
            ['name' => 'Insurance'],
            ['name' => 'Office Practice'],
            ['name' => 'Store Management'],
            ['name' => 'Economice'],
            ['name' => 'Geography'],
            ['name' => 'Government'],
            ['name' => 'History'],
            ['name' => 'English Literature'],
            ['name' => 'Basic Technology'],
            ['name' => 'French'],
            ['name' => 'Music'],
            ['name' => 'CRS'],
            ['name' => 'IRS'],
            ['name' => 'Arabic Study'],
            ['name' => 'Visual Arts'],
            ['name' => 'Technical Drawing'],
            ['name' => 'Food and Nutrition'],
            ['name' => 'Home Management'],
            ['name' => 'Marketing'],
            ['name' => 'Animal Husbandry'],
            ['name' => 'Fishery'],
            ['name' => 'Book Keeping'],
            ['name' => 'Catering and Craft'],
            ['name' => 'Data Processing'],
            ['name' => 'Dyeing and Bleach'],
            ['name' => 'Elect Installation'],
            ['name' => 'Block Bricks Concrete'],
            ['name' => 'Garmet Making'],
            ['name' => 'Painting and Decoration'],
            ['name' => 'PHE'],
            ['name' => 'Building Tect'],
            ['name' => 'Auto Mech'],
            ['name' => 'Business Studies'],
            ['name' => 'Basic Science & Technology'],
            ['name' => 'Pre-vocational Studies'],
            ['name' => 'National Value'],
            ['name' => 'Yoruba'],
            ['name' => 'CCA'],
            ['name' => 'CRS'],
            ['name' => 'IRS'],
            ['name' => 'French'],
            ['name' => 'Arabic'],
            ['name' => 'History'],
            ['name' => 'Social Studies'],
            ['name' => 'Basic Science']
        ];

        foreach ($subjects as $subject) {
            DB::table('subjects')->insert([
                'name' => $subject['name'],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
