<?php

namespace App\Console\Commands;

use App\Services\ExcelImportService;
use Illuminate\Console\Command;

class ImportExcelCommand extends Command
{
    protected $signature = 'import:excel
                            {file : Ruta al archivo Excel o CSV}
                            {--preview : Solo mostrar columnas detectadas}
                            {--replace : Borrar datos actuales antes de importar}';

    protected $description = 'Importar conductores y unidades desde Excel/CSV';

    public function handle(ExcelImportService $importService): int
    {
        $path = $this->argument('file');

        if (! file_exists($path)) {
            $alt = base_path($path);
            if (file_exists($alt)) {
                $path = $alt;
            } else {
                $this->error("No se encontró el archivo: {$this->argument('file')}");

                return self::FAILURE;
            }
        }

        if ($this->option('preview')) {
            $preview = $importService->preview($path, 3);

            $this->info('Columnas en el Excel:');
            $this->line(implode(' | ', $preview['headers']));
            $this->newLine();
            $this->info('Mapeo detectado:');
            foreach ($preview['mapping'] as $field => $column) {
                $this->line("  {$field} → {$column}");
            }
            $this->newLine();
            $this->info("Total filas de datos: {$preview['total_rows']}");

            if ($preview['sample'] !== []) {
                $this->newLine();
                $this->table(array_keys($preview['sample'][0]), $preview['sample']);
            }

            return self::SUCCESS;
        }

        if ($this->option('replace') && ! $this->confirm('¿Borrar TODOS los conductores y unidades actuales?', false)) {
            $this->warn('Importación cancelada.');

            return self::SUCCESS;
        }

        $this->info('Importando...');
        $result = $importService->import($path, $this->option('replace'));

        $this->info("Conductores importados: {$result['imported_conductores']}");
        $this->info("Unidades importadas: {$result['imported_unidades']}");
        $this->warn("Filas omitidas: {$result['skipped']}");

        foreach ($result['errors'] as $error) {
            $this->error($error);
        }

        return self::SUCCESS;
    }
}
