<?php

require __DIR__ . '/../vendor/autoload.php';

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Database\Capsule\Manager as Capsule;

class MigrateCommand extends Command
{
    protected static $defaultName = 'migrate';

    protected function configure()
    {
        $this->setDescription('Run all pending migrations.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->setUpDatabaseConnection();
        $this->createMigrationHistoryTable();
        $output->writeln("<info>Running migrations...</info>");
        $migrationFiles = glob(__DIR__ . '/../database/*.php');
        sort($migrationFiles); // Ensure migrations run in order
        if (empty($migrationFiles)) {
            $output->writeln("<info>No migrations found to run.</info>");
            return Command::SUCCESS;
        }

        foreach ($migrationFiles as $migrationFile) {
            $migrationClass = basename($migrationFile, '.php');
            $alreadyApplied = Capsule::table('tbl_migration_history')
                ->where('migration_name', $migrationClass)
                ->exists();
            if ($alreadyApplied) {
                $output->writeln("<comment>Migration already applied: {$migrationClass}</comment>");
                continue;
            }

            $output->writeln("<info>Processing file: {$migrationFile}</info>");
            require_once $migrationFile; // Include the migration file

            $className = $this->getClassNameFromFile($migrationFile);
            $output->writeln("<info>Class name: {$className}</info>");
            if (class_exists($className)) {
                try {
                    $migrationInstance = new $className();

                    if (method_exists($migrationInstance, 'up')) {
                        $output->writeln("<info>Before running migration: {$className}</info>");
                        $migrationInstance->up();
                        $output->writeln("<info>After running migration: {$className}</info>");
                        
                        Capsule::table('tbl_migration_history')->insert([
                            'migration_name' => $migrationClass,
                            'applied_at' => Carbon::now(),
                        ]);

                        $output->writeln("<info>Migration applied: {$className}</info>");
                        logActivity("Migration applied successfully: {$className}");
                    } else {
                        $output->writeln("<error>Method 'up' not found in class: {$className}</error>");
                    }
                } catch (\Exception $e) {
                    $output->writeln("<error>Error running migration {$className}: {$e->getMessage()}</error>");
                    logActivity("Error applying migration: {$className}. Error: {$e->getMessage()}");
                }
            } else {
                $output->writeln("<error>Class not found: {$className}</error>");
            }
        }

        return Command::SUCCESS;
    }

    protected function createMigrationHistoryTable()
    {

        try {
            $exists = Capsule::schema()->hasTable('tbl_migration_history');
            if ($exists) {
                echo "Table already exists.\n";
            } else {
                echo "Table does not exist. Creating...\n";
                Capsule::schema()->create('tbl_migration_history', function (Blueprint $table) {
                    $table->increments('id');
                    $table->string('migration_name');
                    $table->timestamp('applied_at')->default(Capsule::raw('CURRENT_TIMESTAMP'));
                });
                echo "Table created successfully.\n";
            }
        } catch (\Exception $e) {
            echo "Exception caught: " . $e->getMessage() . "\n";
        }
    }

    protected function setUpDatabaseConnection()
    {
        include(__DIR__ . '/../../../../configuration.php');
        if (isset($db_host) && isset($db_username) && isset($db_password) && isset($db_name)) {
            echo "Configuration variables loaded successfully.\n";
        } else {
            echo "Configuration variables not loaded.\n";
        }

        $capsule = new Capsule;
        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => $db_host,  // Access global variable from configuration.php
            'port' => $db_port ?: '3306',  // Default to 3306 if no port is specified
            'database' => $db_name,  // Access global variable from configuration.php
            'username' => $db_username,  // Access global variable from configuration.php
            'password' => $db_password,  // Access global variable from configuration.php
            'charset' => $mysql_charset ?: 'utf8',  // Default to utf8 if no charset is specified
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        $connection = $capsule->getConnection();
        try {
            $connection->getPdo();
            echo "Database connection successful.\n";
        } catch (\PDOException $e) {
            echo "Database connection failed: " . $e->getMessage() . "\n";
        }
    }


    protected function getClassNameFromFile($file)
    {
        // Get the base filename without path or extension
        $fileName = basename($file, '.php');

        // Remove the timestamp prefix (digits and underscores)
        $classNameWithoutTimestamp = preg_replace('/^\d+_/', '', $fileName);

        // Convert the class name to CamelCase
        $className = str_replace('_', '', ucwords($classNameWithoutTimestamp, '_'));
        $className=preg_replace('/^[0-9]+/', '', $className);
        return $className;
    }
}