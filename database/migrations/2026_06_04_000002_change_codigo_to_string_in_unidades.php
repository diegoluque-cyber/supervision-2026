<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unidades', function (Blueprint $table) {
            // Cambiar codigo de unsignedInteger a string para soportar P112, A421, etc.
            $table->string('codigo', 30)->change();
        });
    }

    public function down(): void
    {
        Schema::table('unidades', function (Blueprint $table) {
            $table->unsignedInteger('codigo')->change();
        });
    }
};
