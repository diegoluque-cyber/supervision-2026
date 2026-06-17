<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Conductores — Taxi ALÓ45</title>
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
            border-bottom: 2px solid #00983F;
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
            color: #00983F;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 8px 10px;
            text-align: left;
            vertical-align: middle;
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
        .badge-activo { background-color: #d1fae5; color: #065f46; }
        .badge-inactivo { background-color: #f3f4f6; color: #374151; }
        .badge-suspendido { background-color: #fee2e2; color: #991b1b; }
        .footer {
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
            margin-top: 50px;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-title">
            <h1>TAXI ALÓ45</h1>
            <p>Reporte de Conductores</p>
        </div>
        <div class="meta-info">
            <p>Fecha de impresión: {{ now()->format('d/m/Y H:i') }}</p>
            <p>Generado por: {{ auth()->user()->name }}</p>
        </div>
    </div>

    <div class="report-title">
        <h2>Padrón Oficial de Conductores</h2>
        @if($search)
            <p style="margin:5px 0 0 0; color:#4b5563;">Filtro de búsqueda: "{{ $search }}"</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 35%;">Nombre Completo</th>
                <th style="width: 15%;">DNI</th>
                <th style="width: 15%;">Licencia</th>
                <th style="width: 15%;">Teléfono</th>
                <th style="width: 15%;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($conductores as $index => $c)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="font-weight: bold;">{{ $c->nombre }}</td>
                    <td>{{ $c->dni }}</td>
                    <td>{{ $c->licencia ?? '—' }}</td>
                    <td>{{ $c->telefono ?? '—' }}</td>
                    <td>
                        <span class="badge badge-{{ $c->estado }}">
                            {{ $c->estado }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #9ca3af; font-style: italic;">
                        No se encontraron conductores.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Taxi ALÓ45 © {{ date('Y') }} — Arequipa, Perú</p>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
