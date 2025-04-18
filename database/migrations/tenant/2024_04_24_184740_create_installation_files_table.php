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
        Schema::create('installation_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('installation_id');
            $table->tinyInteger('type')->default(1);
            $table->string('file', 200);
            $table->string('title', 200)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installation_files');
    }
};
