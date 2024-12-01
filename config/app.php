<?php

return [
    'name' => env('APP_MODULE_NAME') ?? 'Module Name',
    'description' => 'Description for Module Name',
    'author' => env('AUTHOR') ?? 'Your Name',
    'version' => env('VERSION') ?? '1.0.0',
    'enabled' => env('APP_ENABLE') ?? true,
    'fields' => []
];