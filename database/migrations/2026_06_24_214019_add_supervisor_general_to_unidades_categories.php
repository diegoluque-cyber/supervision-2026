<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ampliar el enum para incluir 'supervisor_general'
        DB::statement("ALTER TABLE unidades MODIFY COLUMN categoria ENUM('supervision', 'san_pablo', 'cerro_verde', 'mitsui', 'supervisor', 'conductor', 'supervisor_general', 'otros') NOT NULL");

        // 2. Asignar categoría 'supervisor_general' a la unidad 1015
        DB::table('unidades')->where('codigo', '1015')->update(['categoria' => 'supervisor_general']);
    }

    public function down(): void
    {
        // Revertir a conductor
        DB::table('unidades')->where('categoria', 'supervisor_general')->update(['categoria' => 'conductor']);

        // Revertir la columna categoria
        DB::statement("ALTER TABLE unidades MODIFY COLUMN categoria ENUM('supervision', 'san_pablo', 'cerro_verde', 'mitsui', 'supervisor', 'conductor', 'otros') NOT NULL");
    }
};
