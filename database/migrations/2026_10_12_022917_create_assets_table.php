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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string("code")->unique();
            $table->string("name");
            $table->unsignedBigInteger("category_id");
            $table->unsignedBigInteger("room_id");
            $table->year("procurement_year");
            $table->enum("condition", ["good", "damaged", "under_repair"])->default("good");
            $table->timestamps();

            $table->foreign("category_id")
                ->references("id")
                ->on("asset_categories")
                ->cascadeOnDelete();

            $table->foreign("room_id")
                ->references("id")
                ->on("rooms")
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
