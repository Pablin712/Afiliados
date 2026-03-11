<?php

return [
    'title' => 'Membresias',
    'columns' => [
        'id' => 'ID',
        'user' => 'Usuario',
        'membership_type' => 'Tipo de Membresia',
        'status' => 'Estado',
        'started_at' => 'Inicio',
        'expires_at' => 'Vencimiento',
        'created_at' => 'Creado En',
    ],
    'statuses' => [
        'active' => 'Activa',
        'free' => 'Gratis',
        'expired' => 'Vencida',
        'pending_payment' => 'Pago pendiente',
    ],
    'buttons' => [
        'manage_membership_types' => 'Gestionar tipos de membresia',
        'apply_filters' => 'Aplicar filtros',
        'clear_filters' => 'Limpiar',
    ],
    'filters' => [
        'all_statuses' => 'Todos los estados',
        'all_types' => 'Todos los tipos',
    ],
    'messages' => [
        'description' => 'Listado de membresias con usuario, tipo, estado y fechas clave para seguimiento.',
        'empty' => 'No hay registros disponibles.',
        'report_generated_at' => 'Generado en',
        'report_permission' => 'No cuentas con el permiso :permission para exportar reportes.',
        'permission_key' => 'report memberships',
    ],
];
