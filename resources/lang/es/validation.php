<?php

return [
    'required' => 'El campo :attribute es requerido.',
    'email' => 'El campo :attribute debe ser un correo electrónico válido.',
    'min' => 'El campo :attribute debe tener al menos :min caracteres.',
    'max' => 'El campo :attribute no debe exceder :max caracteres.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'unique' => 'El valor :attribute ya ha sido utilizado.',
    'numeric' => 'El campo :attribute debe ser un número.',
    'password' => 'La contraseña es incorrecta.',

    'custom' => [
        'phone' => [
            'regex' => 'El telefono debe estar en formato internacional valido (ej: +12025550123).',
        ],
    ],

    'attributes' => [
        'name' => 'nombre',
        'email' => 'correo electrónico',
        'password' => 'contraseña',
        'phone' => 'teléfono',
        'identification' => 'identificación',
        'sponsor_id' => 'patrocinador',
        'commission_rate' => 'tasa de comisión',
        'membership_type_id' => 'tipo de membresía',
    ],
];
