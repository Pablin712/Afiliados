<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Auditoria</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111827; }
        h1 { margin: 0 0 8px 0; font-size: 18px; }
        .meta { margin-bottom: 12px; color: #374151; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 5px; vertical-align: top; }
        th { background: #eef2ff; font-size: 9px; text-transform: uppercase; }
        td { font-size: 9px; }
    </style>
</head>
<body>
    <h1>Reporte de Auditoria</h1>
    <div class="meta">
        Generado: {{ $generatedAt->format('Y-m-d H:i:s') }}<br>
        Total de registros: {{ $records->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Modulo</th>
                <th>Accion</th>
                <th>Metodo</th>
                <th>Ruta</th>
                <th>URL</th>
                <th>IP</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
        @foreach($records as $action)
            <tr>
                <td>{{ $action->id }}</td>
                <td>{{ $action->user?->name ?? 'Sistema' }}</td>
                <td>{{ $action->module }}</td>
                <td>{{ $action->action }}</td>
                <td>{{ $action->method }}</td>
                <td>{{ $action->route }}</td>
                <td>{{ $action->url }}</td>
                <td>{{ $action->ip_address }}</td>
                <td>{{ optional($action->created_at)->format('Y-m-d H:i:s') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
