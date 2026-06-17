<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SupervisionSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->delete();

        User::create([
            'name' => 'Supervisor Alo 45',
            'username' => 'supervisor',
            'email' => 'supervisor@alo45.pe',
            'email_verified_at' => now(),
            'password' => Hash::make('Alo45@2026'),
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('unidades')->truncate();
        DB::table('conductores')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $categorias = ['supervision', 'san_pablo', 'cerro_verde', 'mitsui', 'otros'];
        $marcas = ['Toyota', 'Hyundai', 'Kia', 'Chevrolet', 'Nissan'];
        $modelos = ['Yaris', 'Accent', 'Rio', 'Sail', 'Versa'];
        $estadosUnidad = ['activo', 'activo', 'activo', 'inactivo', 'mantenimiento'];
        $estadosConductor = ['activo', 'activo', 'activo', 'inactivo', 'suspendido'];

        $now = now();

        for ($chunk = 0; $chunk < 6; $chunk++) {
            $conductoresBatch = [];
            $start = $chunk * 500 + 1;
            $end = min(($chunk + 1) * 500, 3000);

            for ($i = $start; $i <= $end; $i++) {
                $conductoresBatch[] = [
                    'nombre' => 'Conductor '.$i.' '.chr(65 + ($i % 26)),
                    'dni' => sprintf('%08d', 10000000 + $i),
                    'licencia' => 'Q'.sprintf('%08d', $i),
                    'telefono' => '9'.str_pad((string) ($i % 100000000), 8, '0', STR_PAD_LEFT),
                    'estado' => $estadosConductor[$i % count($estadosConductor)],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('conductores')->insert($conductoresBatch);
        }

        $conductorIds = DB::table('conductores')->orderBy('id')->pluck('id');

        for ($chunk = 0; $chunk < 6; $chunk++) {
            $unidadesBatch = [];
            $start = $chunk * 500 + 1;
            $end = min(($chunk + 1) * 500, 3000);

            for ($i = $start; $i <= $end; $i++) {
                $letras = chr(65 + (($i / 1000) % 26)).chr(65 + (($i / 100) % 26));
                $unidadesBatch[] = [
                    'codigo' => $i,
                    'placa' => sprintf('%s-%03d', $letras, $i % 1000),
                    'categoria' => $categorias[$i % count($categorias)],
                    'estado' => $estadosUnidad[$i % count($estadosUnidad)],
                    'conductor_id' => $conductorIds[$i - 1],
                    'marca' => $marcas[$i % count($marcas)],
                    'modelo' => $modelos[$i % count($modelos)],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('unidades')->insert($unidadesBatch);
        }
    }
}
