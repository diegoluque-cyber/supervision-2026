<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ampliar el enum para incluir 'supervisor' y 'conductor'
        DB::statement("ALTER TABLE unidades MODIFY COLUMN categoria ENUM('supervision', 'san_pablo', 'cerro_verde', 'mitsui', 'supervisor', 'conductor', 'otros') NOT NULL");

        // 2. Definir los códigos de unidades de supervisores
        $supervisorCodes = ['1390', '84', '01', '1', '10', '1426', '1484', '1998', '2137', '287', '265', '531', '1079', '28', '1150', '1050', '980', '81'];

        // Comprobar la existencia del móvil 47 o P47
        $u47 = DB::table('unidades')->where('codigo', '47')->first();
        if ($u47) {
            $supervisorCodes[] = '47';
        } else {
            $uP47 = DB::table('unidades')->where('codigo', 'P47')->first();
            if ($uP47) {
                $supervisorCodes[] = 'P47';
            } else {
                DB::table('unidades')->insert([
                    'codigo' => '47',
                    'placa' => 'V3G-477',
                    'categoria' => 'otros',
                    'estado' => 'activo',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $supervisorCodes[] = '47';
            }
        }

        // 3. Asignar categoría 'supervisor' a las 18 unidades (incluidos los dueños, excepto 1015 que queda como otros -> conductor)
        DB::table('unidades')->whereIn('codigo', $supervisorCodes)->update(['categoria' => 'supervisor']);

        // 4. Asignar categoría 'conductor' a todas las unidades que actualmente tienen 'otros' (esto incluye a la 1015)
        DB::table('unidades')->where('categoria', 'otros')->update(['categoria' => 'conductor']);
    }

    public function down(): void
    {
        // Revertir a otros
        DB::table('unidades')->where('categoria', 'conductor')->update(['categoria' => 'otros']);
        DB::table('unidades')->where('categoria', 'supervisor')->update(['categoria' => 'otros']);

        // Revertir la columna categoria
        DB::statement("ALTER TABLE unidades MODIFY COLUMN categoria ENUM('supervision', 'san_pablo', 'cerro_verde', 'mitsui', 'otros') NOT NULL");
    }
};
