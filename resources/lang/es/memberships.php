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
        'actions' => 'Acciones',
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
        'save_membership' => 'Guardar cambios',
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
        'updated' => 'La membresia fue actualizada correctamente.',
        'invalid_paid_status' => 'No puedes dejar estado free en una membresia de pago. Usa un estado valido.',
        'invalid_dates' => 'La fecha de vencimiento no puede ser menor que la fecha de inicio.',
    ],
];
