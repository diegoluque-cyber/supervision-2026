<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operativos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->dateTime('fecha');
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::table('faltas', function (Blueprint $table) {
            $table->foreignId('operativo_id')->nullable()->constrained('operativos')->nullOnDelete();
        });

        Schema::table('papeletas', function (Blueprint $table) {
            $table->foreignId('operativo_id')->nullable()->constrained('operativos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('papeletas', function (Blueprint $table) {
            $table->dropForeign(['operativo_id']);
            $table->dropColumn('operativo_id');
        });

        Schema::table('faltas', function (Blueprint $table) {
            $table->dropForeign(['operativo_id']);
            $table->dropColumn('operativo_id');
        });

        Schema::dropIfExists('operativos');
    }
};
