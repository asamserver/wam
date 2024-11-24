<?php

return [
    'name' => getenv('MODULE') ?? 'kyc',
    'description' => 'Description for kyc',
    'author' => getenv('AUTHOR') ?? 'Your Name',
    'version' => getenv('VERSION') ?? '1.0.0',
    'enabled' => getenv('APP_ENABLE') ?? true,
];
