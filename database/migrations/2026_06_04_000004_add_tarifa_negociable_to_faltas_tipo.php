<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // MySQL: modificar el ENUM para agregar tarifa_negociable
            DB::statement("ALTER TABLE faltas MODIFY COLUMN tipo ENUM('observacion','falta_grave','expulsado','tarifa_negociable','libre') NOT NULL DEFAULT 'observacion'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE faltas MODIFY COLUMN tipo ENUM('observacion','falta_grave','expulsado','libre') NOT NULL DEFAULT 'observacion'");
        }
    }
};
