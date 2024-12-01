<?php

require __DIR__ . '/../vendor/autoload.php';
if(env('APP_ENV') == 'production') {
    require_once __DIR__ . '/../../../../init.php';
}

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WHMCS\Database\Capsule;

class MigrateCommand extends Command
{
    protected static $defaultName = 'migrate';

    protected function configure()
    {
        $this->setDescription('Run all pending migrations.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        echo "About to create migration history table...\n";
        $this->createMigrationHistoryTable();
        echo "Finished creating migration history table...\n";
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

            if (class_exists($className)) {
                try {
                    $migrationInstance = new $className();

                    // Run the 'up' method if it exists
                    if (method_exists($migrationInstance, 'up')) {
                        $migrationInstance->up();

                        // Record the migration in history
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
        echo "Checking if migration history table exists...\n";

        try {
            echo "Before hasTable call...\n";
            $exists = Capsule::schema()->hasTable('tbl_migration_history');
            echo "After hasTable call...\n";

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