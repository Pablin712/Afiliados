<?php

return [
    'title' => 'Reporte de Membresias',
    'subtitle' => 'Resumen de usuarios y membresias en el periodo seleccionado.',
    'apply' => 'Aplicar rango',
    'export_pdf' => 'Exportar PDF',
    'cards' => [
        'total_users' => 'Usuarios totales (a la fecha)',
        'new_users' => 'Usuarios nuevos en el periodo',
        'paying_users' => 'Usuarios con membresia de pago',
        'free_users' => 'Usuarios en plan free',
        'upgrades' => 'Altas / mejoras en el periodo',
        'non_renewed' => 'No renovaron en el periodo',
    ],
    'sections' => [
        'type_breakdown' => 'Usuarios por tipo de membresia',
        'status_breakdown' => 'Usuarios por estado de membresia',
        'upgrades' => 'Nuevas membresias de pago en el periodo',
        'non_renewed' => 'Usuarios que no renovaron en el periodo',
    ],
    'columns' => [
        'type' => 'Tipo',
        'status' => 'Estado',
        'total' => 'Total',
        'percent' => 'Porcentaje',
        'user' => 'Usuario',
        'email' => 'Email',
        'membership_type' => 'Tipo de membresia',
        'started_at' => 'Inicio',
        'previous_type' => 'Tipo anterior',
        'downgraded_at' => 'Fecha de baja',
    ],
    'statuses' => [
        'active' => 'Activa',
        'free' => 'Gratis',
        'expired' => 'Vencida',
        'pending_payment' => 'Pago pendiente',
    ],
    'messages' => [
        'empty' => 'No hay registros en este periodo.',
        'report_generated_at' => 'Generado en',
        'non_renewed_hint' => 'Detectado a partir del historial de auditoria: usuarios cuya membresia paso a estado free en el periodo.',
        'permission_key' => 'report memberships',
    ],
];
