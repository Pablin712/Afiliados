<?php

return [
    'distribution_mode' => env('SCANNER_DISTRIBUTION_MODE', 'auto'),
    'metaeditor_path' => env('SCANNER_METAEDITOR_PATH'),
    'compile_timeout_seconds' => (int) env('SCANNER_COMPILE_TIMEOUT_SECONDS', 60),
    'precompiled_disk' => env('SCANNER_PRECOMPILED_DISK', 'public'),
    'precompiled_directory' => env('SCANNER_PRECOMPILED_DIRECTORY', 'scanners-bin'),
];
