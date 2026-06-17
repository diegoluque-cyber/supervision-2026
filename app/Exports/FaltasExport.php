<?php

namespace App\Exports;

use App\Models\Falta;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FaltasExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    private $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Falta::query()->with(['unidad', 'conductor']);

        if ($this->filters['categoria'] ?? null) {
            $query->where('categoria', $this->filters['categoria']);
        }

        if ($this->filters['search'] ?? null) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('unidad', function ($uq) use ($search) {
                    $uq->where('codigo', $search)->orWhere('placa', 'like', "%{$search}%");
                })->orWhereHas('conductor', function ($cq) use ($search) {
                    $cq->where('nombre', 'like', "%{$search}%")->orWhere('dni', 'like', "%{$search}%");
                });
            });
        }

        if ($this->filters['fecha_desde'] ?? null) {
            $query->whereDate('fecha_falta', '>=', $this->filters['fecha_desde']);
        }

        if ($this->filters['fecha_hasta'] ?? null) {
            $query->whereDate('fecha_falta', '<=', $this->filters['fecha_hasta']);
        }

        return $query->orderByDesc('fecha_falta');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Fecha',
            'Unidad',
            'Placa',
            'Conductor',
            'DNI Conductor',
            'Tipo de Falta',
            'Descripción',
            'Supervisor',
        ];
    }

    public function map($falta): array
    {
        return [
            $falta->id,
            $falta->fecha_falta ? $falta->fecha_falta->format('d/m/Y H:i') : '',
            $falta->unidad?->codigo ?? '',
            $falta->unidad?->placa ?? '',
            $falta->conductor?->nombre ?? 'Sin conductor',
            $falta->conductor?->dni ?? '',
            $falta->tipo_label,
            $falta->descripcion,
            $falta->supervisor ?? '',
        ];
    }
}
