<?php

return [
    'metaeditor_path' => env('SCANNER_METAEDITOR_PATH'),
    'compile_timeout_seconds' => (int) env('SCANNER_COMPILE_TIMEOUT_SECONDS', 60),
];
