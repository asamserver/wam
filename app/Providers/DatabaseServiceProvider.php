<?php
use Illuminate\Database\Capsule\Manager as Capsule;

class DatabaseServiceProvider
{
    public static function boot()
    {
        $config = require __DIR__ . '/../../config/database.php';
        $connection = $config['default'];
        $connectionConfig = $config['connections'][$connection];
        $capsule = new Capsule();
        $capsule->addConnection([
            'driver'    => $connectionConfig['driver'],
            'host'      => $connectionConfig['host'],
            'port'      => $connectionConfig['port'],
            'database'  => $connectionConfig['database'],
            'username'  => $connectionConfig['username'],
            'password'  => $connectionConfig['password'],
            'charset'   => $connectionConfig['charset'],
            'collation' => $connectionConfig['collation'],
            'prefix'    => $connectionConfig['prefix'],
        ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }
}


