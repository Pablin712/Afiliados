<?php

return [
    'title' => 'Tipos de Membresia',
    'columns' => [
        'id' => 'ID',
        'name' => 'Nombre',
        'affiliates_required' => 'Afiliados Requeridos',
        'cost' => 'Costo',
        'profit' => 'Ganancia',
        'created_at' => 'Creado En',
        'actions' => 'Acciones',
    ],
    'forms' => [
        'create_title' => 'Crear Tipo de Membresia',
        'edit_title' => 'Editar Tipo de Membresia',
        'delete_title' => 'Eliminar Tipo de Membresia',
    ],
    'buttons' => [
        'create' => 'Crear',
        'update' => 'Actualizar',
        'edit' => 'Editar',
        'delete' => 'Eliminar',
        'cancel' => 'Cancelar',
        'cancel_edit' => 'Cancelar edicion',
        'back_to_memberships' => 'Volver a Membresias',
    ],
    'messages' => [
        'empty' => 'No hay tipos de membresia disponibles.',
        'created' => 'Tipo de membresia creado correctamente.',
        'updated' => 'Tipo de membresia actualizado correctamente.',
        'deleted' => 'Tipo de membresia eliminado correctamente.',
        'delete_blocked' => 'No se puede eliminar el tipo porque tiene membresias asociadas.',
        'confirm_delete' => 'Esta accion eliminara el tipo de membresia. Deseas continuar?',
        'confirm_delete_modal' => 'Esta accion eliminara el tipo de membresia seleccionado.',
    ],
    'types' => [
        'free' => 'Gratis',
        'customer' => 'Cliente',
        'beginner' => 'Principiante',
        'explorer' => 'Explorador',
        'professional' => 'Profesional',
        'elite' => 'Élite',
    ],
];
