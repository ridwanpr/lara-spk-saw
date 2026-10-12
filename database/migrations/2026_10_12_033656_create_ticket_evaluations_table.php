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
        Schema::create('ticket_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId("ticket_id")->constrained("repair_tickets")->cascadeOnDelete();
            $table->foreignId("criteria_id")->constrained("criterias")->cascadeOnDelete();
            $table->foreignId("sub_criteria_id")->constrained("sub_criterias")->cascadeOnDelete();
            $table->integer('score');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_evaluations');
    }
};
