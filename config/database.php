<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => getenv('DB_CONNECTION', 'mysql'),

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => getenv('DATABASE_URL'),
            'database' => getenv('DB_DATABASE', __DIR__.'/../database/'.'database.sqlite'),
            'prefix' => '',
            'foreign_key_constraints' => getenv('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => getenv('DATABASE_URL'),
            'host' => getenv('DB_HOST', '127.0.0.1'),
            'port' => getenv('DB_PORT', '3306'),
            'database' => getenv('DB_DATABASE', 'forge'),
            'username' => getenv('DB_USERNAME', 'forge'),
            'password' => getenv('DB_PASSWORD', ''),
            'unix_socket' => getenv('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => getenv('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => getenv('DATABASE_URL'),
            'host' => getenv('DB_HOST', '127.0.0.1'),
            'port' => getenv('DB_PORT', '5432'),
            'database' => getenv('DB_DATABASE', 'forge'),
            'username' => getenv('DB_USERNAME', 'forge'),
            'password' => getenv('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => getenv('DATABASE_URL'),
            'host' => getenv('DB_HOST', 'localhost'),
            'port' => getenv('DB_PORT', '1433'),
            'database' => getenv('DB_DATABASE', 'forge'),
            'username' => getenv('DB_USERNAME', 'forge'),
            'password' => getenv('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => getenv('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => getenv('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => getenv('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => getenv('REDIS_CLUSTER', 'redis'),
            'prefix' => getenv('REDIS_PREFIX', Str::slug(getenv('MODULE', 'laravel'), '_').'_database_'),
        ],

        'default' => [
            'url' => getenv('REDIS_URL'),
            'host' => getenv('REDIS_HOST', '127.0.0.1'),
            'username' => getenv('REDIS_USERNAME'),
            'password' => getenv('REDIS_PASSWORD'),
            'port' => getenv('REDIS_PORT', '6379'),
            'database' => getenv('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => getenv('REDIS_URL'),
            'host' => getenv('REDIS_HOST', '127.0.0.1'),
            'username' => getenv('REDIS_USERNAME'),
            'password' => getenv('REDIS_PASSWORD'),
            'port' => getenv('REDIS_PORT', '6379'),
            'database' => getenv('REDIS_CACHE_DB', '1'),
        ],

    ],

];
