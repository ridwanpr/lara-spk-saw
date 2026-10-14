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
        Schema::table('sub_criterias', function (Blueprint $table) {
            $table->unique(['criteria_id', 'crisp_value']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_criterias', function (Blueprint $table) {
            $table->dropUnique(['criteria_id', 'crisp_value']);
        });
    }
};
