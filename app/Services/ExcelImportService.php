<?php

namespace App\Services;

use App\Models\Conductor;
use App\Models\Unidad;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ExcelImportService
{
    /**
     * Columnas del Excel: UNIDAD, DNI, NOMBRES COMPLETOS (o separados), TELEFONO/CELULAR
     * Soporta nombres en una sola columna o en columnas separadas:
     *   nombre / apellido_paterno / apellido_materno
     */
    private const COLUMN_ALIASES = [
        'unidad' => [
            'unidad', 'n_unidad', 'nro_unidad', 'num_unidad', 'numero_unidad',
            'cod_unidad', 'codigo_unidad', 'codigo', 'cod', 'n', 'no', 'nro',
            'numero', 'numero_de_unidad', 'n_de_unidad', 'unid',
        ],
        'dni' => [
            'dni', 'documento', 'doc', 'nro_dni', 'num_dni',
            'numero_de_documento', 'n_dni', 'cedula', 'id',
        ],
        'nombre' => [
            'nombres_completos', 'nombres_completos_mas_su_dni',
            'nombre_completo', 'nombres', 'nombre', 'conductor', 'chofer',
            'nombres_y_apellidos', 'apellidos_y_nombres',
        ],
        'apellido_paterno' => [
            'apellido_paterno', 'ap_paterno', 'primer_apellido',
            'apellido1', 'apellido_1', 'ape_paterno', 'paterno',
            'a_paterno', 'ape1',
        ],
        'apellido_materno' => [
            'apellido_materno', 'ap_materno', 'segundo_apellido',
            'apellido2', 'apellido_2', 'ape_materno', 'materno',
            'a_materno', 'ape2',
        ],
        'telefono' => [
            'telefono', 'celular', 'telefono_celular', 'celular_telefono',
            'tel', 'cel', 'movil', 'phone', 'numero_celular', 'nro_celular',
            'num_celular', 'numero_telefono', 'nro_telefono',
            'telefono_o_celular', 'celular_o_telefono',
            'telefono_o_celular_1',  // normalizado desde "TELEFONO O CELULAR"
        ],
        // Campos del vehículo
        'vehi_marca'  => ['vehi_marca', 'marca', 'marca_vehiculo'],
        'vehi_modelo' => ['vehi_modelo', 'modelo', 'modelo_vehiculo'],
        'vehi_color'  => ['vehi_color', 'color', 'color_vehiculo'],
        'vehi_anio'   => ['vehi_anio', 'anio', 'año', 'year', 'vehi_ano'],
        'vehi_placa'  => ['vehi_placa', 'placa', 'placa_vehiculo'],
    ];

    public function preview(string $path, int $sampleRows = 5): array
    {
        $rows = $this->readRows($path);

        if ($rows === []) {
            return [
                'headers' => [],
                'mapping' => [],
                'unmapped' => [],
                'sample' => [],
                'total_rows' => 0,
            ];
        }

        $headers = array_shift($rows);
        $normalized = $this->normalizeHeaders($headers);
        $mapping = $this->detectMapping($normalized);

        $sample = [];
        foreach (array_slice($rows, 0, $sampleRows) as $row) {
            $sample[] = $this->mapRow($row, $normalized, $mapping);
        }

        $mappedKeys = array_values(array_filter($mapping));

        return [
            'headers' => $headers,
            'normalized_headers' => $normalized,
            'mapping' => $mapping,
            'unmapped' => array_values(array_diff($normalized, $mappedKeys)),
            'sample' => $sample,
            'total_rows' => count($rows),
        ];
    }

    public function import(string $path, bool $replace = false): array
    {
        $rows = $this->readRows($path);

        if ($rows === []) {
            return ['imported_conductores' => 0, 'imported_unidades' => 0, 'skipped' => 0, 'errors' => ['El archivo está vacío.']];
        }

        $headers = array_shift($rows);
        $normalized = $this->normalizeHeaders($headers);
        $mapping = $this->detectMapping($normalized);

        if (! isset($mapping['dni']) && ! isset($mapping['nombre']) && ! isset($mapping['apellido_paterno'])) {
            return [
                'imported_conductores' => 0,
                'imported_unidades'    => 0,
                'skipped'              => 0,
                'errors'               => ['No se detectó columna DNI ni NOMBRES. Revisa la primera fila del Excel.'],
            ];
        }

        if ($replace) {
            $this->clearTables();
        }

        $stats = ['imported_conductores' => 0, 'imported_unidades' => 0, 'skipped' => 0, 'errors' => []];

        foreach ($rows as $index => $row) {
            $data = $this->mapRow($row, $normalized, $mapping);

            if ($this->isEmptyRow($data)) {
                $stats['skipped']++;

                continue;
            }

            try {
                DB::transaction(function () use ($data, $index, &$stats) {
                    $conductor = $this->upsertConductor($data, $index + 2);
                    $stats['imported_conductores']++;

                    if ($this->hasUnidadData($data)) {
                        $this->upsertUnidad($data, $conductor);
                        $stats['imported_unidades']++;
                    }
                });
            } catch (\Throwable $e) {
                $stats['skipped']++;
                if (count($stats['errors']) < 10) {
                    $stats['errors'][] = 'Fila '.($index + 2).': '.$e->getMessage();
                }
            }
        }

        return $stats;
    }

    private function clearTables(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Unidad::query()->truncate();
        Conductor::query()->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function readRows(string $path): array
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($extension === 'csv') {
            return $this->readCsv($path);
        }

        $data = Excel::toArray(new class {}, $path);

        return $data[0] ?? [];
    }

    private function readCsv(string $path): array
    {
        $rows = [];
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return [];
        }

        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    private function normalizeHeaders(array $headers): array
    {
        return array_map(fn ($header) => $this->normalizeHeader((string) $header), $headers);
    }

    private function normalizeHeader(string $header): string
    {
        $header = trim($header);
        $header = Str::ascii($header);
        $header = strtolower($header);
        $header = preg_replace('/[^a-z0-9]+/', '_', $header) ?? $header;

        return trim($header, '_');
    }

    private function detectMapping(array $normalizedHeaders): array
    {
        $mapping = [];

        foreach (self::COLUMN_ALIASES as $field => $aliases) {
            // 1. Coincidencia exacta
            foreach ($aliases as $alias) {
                if (in_array($alias, $normalizedHeaders, true)) {
                    $mapping[$field] = $alias;
                    break;
                }
            }

            if (isset($mapping[$field])) {
                continue;
            }

            // 2. El header contiene alguno de los aliases
            foreach ($normalizedHeaders as $header) {
                foreach ($aliases as $alias) {
                    if (str_contains($header, $alias) || str_contains($alias, $header)) {
                        $mapping[$field] = $header;
                        break 2;
                    }
                }
            }
        }

        return $mapping;
    }

    private function mapRow(array $row, array $headers, array $mapping): array
    {
        $assoc = [];
        foreach ($headers as $i => $header) {
            $value = isset($row[$i]) ? trim((string) $row[$i]) : '';
            if ($value !== '') {
                $assoc[$header] = $value;
            }
        }

        $mapped = [];
        foreach ($mapping as $field => $header) {
            $mapped[$field] = $assoc[$header] ?? '';
        }

        return $mapped;
    }

    private function isEmptyRow(array $data): bool
    {
        $tieneNombre = ($data['nombre'] ?? '') !== ''
            || ($data['apellido_paterno'] ?? '') !== ''
            || ($data['apellido_materno'] ?? '') !== '';

        return ! $tieneNombre && ($data['dni'] ?? '') === '';
    }

    private function hasUnidadData(array $data): bool
    {
        return ($data['unidad'] ?? '') !== '';
    }

    private function upsertConductor(array $data, int $rowNumber): Conductor
    {
        $dni = preg_replace('/\D/', '', $data['dni'] ?? '') ?: 'TMP'.str_pad((string) $rowNumber, 8, '0', STR_PAD_LEFT);

        // Si tiene columnas separadas (nombre + ap. paterno + ap. materno), las une.
        // Si tiene una sola columna "nombre", la usa directamente.
        $partes = array_filter([
            trim($data['nombre'] ?? ''),
            trim($data['apellido_paterno'] ?? ''),
            trim($data['apellido_materno'] ?? ''),
        ]);

        $nombreCompleto = implode(' ', $partes) ?: 'Conductor '.$dni;

        return Conductor::updateOrCreate(
            ['dni' => $dni],
            [
                'nombre'   => $nombreCompleto,
                'licencia' => null,
                'telefono' => $data['telefono'] ?? null,
                'estado'   => 'activo',
            ]
        );
    }

    private function upsertUnidad(array $data, Conductor $conductor): Unidad
    {
        // Guardar el código exactamente como viene del Excel (P112, A421, 518, etc.)
        $codigo = strtoupper(trim($data['unidad'] ?? ''));

        if ($codigo === '') {
            throw new \InvalidArgumentException('Código de unidad vacío.');
        }

        // Placa: usar la del Excel si existe, si no generar una
        $placa = strtoupper(trim($data['vehi_placa'] ?? ''));
        if ($placa === '') {
            $placa = 'U-'.$codigo;
        }

        $marca  = trim($data['vehi_marca'] ?? '') ?: null;
        $modelo = trim($data['vehi_modelo'] ?? '') ?: null;
        $color  = trim($data['vehi_color'] ?? '') ?: null;
        $anio   = (int) trim($data['vehi_anio'] ?? '');
        $anio   = ($anio >= 1990 && $anio <= 2035) ? $anio : null;

        return Unidad::updateOrCreate(
            ['codigo' => $codigo],
            [
                'placa'        => $placa,
                'categoria'    => 'otros',
                'estado'       => 'activo',
                'conductor_id' => $conductor->id,
                'marca'        => $marca,
                'modelo'       => $modelo,
                'color'        => $color,
                'anio'         => $anio,
            ]
        );
    }
}
