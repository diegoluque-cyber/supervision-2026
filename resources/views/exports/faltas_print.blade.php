<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Faltas — Taxi ALÓ45</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.5;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #10b981;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .logo-title h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 900;
            color: #111827;
        }
        .logo-title p {
            margin: 2px 0 0 0;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #10b981;
        }
        .meta-info {
            text-align: right;
            font-size: 10px;
            color: #666;
        }
        .report-title {
            text-align: center;
            margin-bottom: 20px;
        }
        .report-title h2 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            color: #111827;
        }
        .report-title p {
            margin: 5px 0 0 0;
            font-size: 11px;
            color: #4b5563;
        }
        .filters-summary {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 10px 15px;
            margin-bottom: 20px;
            display: grid;
            grid-template-cols: 1fr 1fr;
            gap: 5px;
        }
        .filters-summary div span {
            font-weight: bold;
            color: #374151;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 8px 10px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f3f4f6;
            font-weight: bold;
            color: #374151;
            font-size: 10px;
            text-transform: uppercase;
        }
        tr:nth-child(even) {
            background-color: #fafafa;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-observacion { background-color: #fef3c7; color: #92400e; }
        .badge-falta_grave { background-color: #ffedd5; color: #9a3412; }
        .badge-expulsado { background-color: #fee2e2; color: #991b1b; }
        .badge-tarifa_negociable { background-color: #dbeafe; color: #1e40af; }
        .badge-libre { background-color: #d1fae5; color: #065f46; }
        .badge-cancela_servicios_corporativos { background-color: #f3e8ff; color: #6b21a8; }
        
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px dashed #e5e7eb;
            padding-top: 15px;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
            button {
                display: none;
            }
        }
        .no-print-bar {
            background-color: #1f2937;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-radius: 6px;
        }
        .btn-print {
            background-color: #10b981;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
        }
        .btn-print:hover {
            background-color: #059669;
        }
    </style>
</head>
<body>
    <div class="no-print no-print-bar">
        <span>Vista previa de impresión para PDF. Puedes guardarlo directamente usando la opción de la impresora.</span>
        <button onclick="window.print()" class="btn-print">Imprimir / Guardar PDF</button>
    </div>

    <div class="header">
        <div class="logo-title">
            <h1>Taxi ALÓ45</h1>
            <p>Sistema de Supervisión</p>
        </div>
        <div class="meta-info">
            <p>Generado por: {{ auth()->user()->name }}</p>
            <p>Fecha de Reporte: {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>

    <div class="report-title">
        <h2>Reporte Historial de Faltas</h2>
        <p>Control e incidencias de unidades registradas en plataforma</p>
    </div>

    <div class="filters-summary">
        <div><span>Categoría/Zona:</span> {{ $categoriaLabel }}</div>
        <div><span>Búsqueda:</span> {{ $search ?: 'Todos' }}</div>
        <div><span>Desde:</span> {{ $fecha_desde ? \Carbon\Carbon::parse($fecha_desde)->format('d/m/Y') : 'Inicio' }}</div>
        <div><span>Hasta:</span> {{ $fecha_hasta ? \Carbon\Carbon::parse($fecha_hasta)->format('d/m/Y') : 'Actualidad' }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">ID</th>
                <th style="width: 12%">Fecha</th>
                <th style="width: 8%">Unidad</th>
                <th style="width: 10%">Placa</th>
                <th style="width: 15%">Conductor</th>
                <th style="width: 12%">Tipo de Falta</th>
                <th style="width: 28%">Descripción / Observación</th>
                <th style="width: 10%">Supervisor</th>
            </tr>
        </thead>
        <tbody>
            @forelse($faltas as $f)
                <tr>
                    <td>{{ $f->id }}</td>
                    <td>{{ $f->fecha_falta ? $f->fecha_falta->format('d/m/Y H:i') : '' }}</td>
                    <td style="font-weight: bold; color: #10b981;">#{{ $f->unidad?->codigo }}</td>
                    <td style="font-family: monospace;">{{ $f->unidad?->placa }}</td>
                    <td>
                        {{ $f->conductor?->nombre ?? 'Sin conductor' }}
                        @if($f->conductor?->dni)
                            <div style="font-size: 9px; color: #666;">DNI: {{ $f->conductor->dni }}</div>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-{{ $f->type ?? $f->tipo }}">
                            {{ $f->tipo_label }}
                        </span>
                    </td>
                    <td>{{ $f->descripcion }}</td>
                    <td>{{ $f->supervisor }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #999; padding: 20px;">
                        No se encontraron registros de faltas con los filtros especificados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Este documento es un reporte generado directamente por el Sistema de Supervisión de Taxi ALÓ45 en Arequipa, Perú. Todos los derechos reservados.
    </div>

    <script>
        window.onload = function() {
            // Un pequeño delay para que carguen bien los estilos
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>
