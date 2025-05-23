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
        Schema::table('admin_catalogs', function (Blueprint $table) {
            //
            //$table->string('name_en', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_catalogs', function (Blueprint $table) {
            //
            $table->dropColumn('name_en');
        });
    }
};
