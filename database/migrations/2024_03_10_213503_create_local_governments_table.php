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
        Schema::create('local_governments', function (Blueprint $table) {
            $table->id();
            $table->integer('lg_code');
            $table->string('lg_name');
            $table->timestamps();
        });

        $lgas = [
            [ 'lg_code' => 1, 'lg_name' => 'ADO' ],
            [ 'lg_code' => 2, 'lg_name' => 'EFON' ],
            [ 'lg_code' => 3, 'lg_name' => 'EKITI EAST' ],
            [ 'lg_code' => 4, 'lg_name' => 'EKITI SOUTH-WEST' ],
            [ 'lg_code' => 5, 'lg_name' => 'EKITI WEST' ],
            [ 'lg_code' => 6, 'lg_name' => 'EMURE' ],
            [ 'lg_code' => 7, 'lg_name' => 'GBONYIN' ],
            [ 'lg_code' => 8, 'lg_name' => 'IDO/OSI' ],
            [ 'lg_code' => 9, 'lg_name' => 'IJERO' ],
            [ 'lg_code' => 10, 'lg_name' => 'IKERE' ],
            [ 'lg_code' => 11, 'lg_name' => 'IKOLE' ],
            [ 'lg_code' => 12, 'lg_name' => 'ILEJEMEJE' ],
            [ 'lg_code' => 13, 'lg_name' => 'IRE/IFE I' ],
            [ 'lg_code' => 14, 'lg_name' => 'ISE ORUN' ],
            [ 'lg_code' => 15, 'lg_name' => 'MOBA' ],
            [ 'lg_code' => 16, 'lg_name' => 'OYE' ],
            [ 'lg_code' => 17, 'lg_name' => 'ADO II' ],
            [ 'lg_code' => 18, 'lg_name' => 'ADO III' ],
            [ 'lg_code' => 19, 'lg_name' => 'IRE/IFE II' ],
            [ 'lg_code' => 20, 'lg_name' => 'ADO IV' ]
        ];

        foreach ($lgas as $lga) {
            DB::table('local_governments')->insert([
                'lg_code' => $lga['lg_code'],
                'lg_name' => $lga['lg_name'],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('local_governments');
    }
};
