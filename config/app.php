<?php

return [
    'name' => env('APP_MODULE_NAME') ?? 'env('MODULE_NAME',
    'description' => 'Description for env('MODULE_NAME',
    'author' => env('AUTHOR') ?? 'Your Name',
    'version' => env('VERSION') ?? '1.0.0',
    'enabled' => env('APP_ENABLE') ?? true,
    'fields' => []
];