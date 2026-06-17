<?php

namespace App\Exports;

use App\Models\Unidad;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UnidadesExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    private $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Unidad::query()->with('conductor');

        if ($this->filters['categoria'] ?? null) {
            $query->where('categoria', $this->filters['categoria']);
        }

        if ($this->filters['search'] ?? null) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('codigo', $search)
                  ->orWhere('placa', 'like', "%{$search}%")
                  ->orWhereHas('conductor', function ($cq) use ($search) {
                      $cq->where('nombre', 'like', "%{$search}%")
                         ->orWhere('dni', 'like', "%{$search}%");
                  });
            });
        }

        return $query->orderBy('codigo');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Código',
            'Placa',
            'Categoría',
            'Estado',
            'Conductor',
            'DNI Conductor',
            'Teléfono Conductor',
            'Vehículo',
            'Año',
        ];
    }

    public function map($unidad): array
    {
        return [
            $unidad->id,
            $unidad->codigo,
            $unidad->placa,
            $unidad->categoria_label,
            $unidad->estado_label,
            $unidad->conductor?->nombre ?? 'Sin conductor',
            $unidad->conductor?->dni ?? '',
            $unidad->conductor?->telefono ?? '',
            implode(' ', array_filter([$unidad->marca, $unidad->modelo, $unidad->color])),
            $unidad->anio ?? '',
        ];
    }
}
