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
        Schema::create('installation_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('installation_id');
            $table->unsignedBigInteger('user_id');
            $table->string('notes', 1000);
            $table->tinyInteger('status')->default(0); /// 2: Canceled; 3: Posposed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installation_notes');
    }
};
