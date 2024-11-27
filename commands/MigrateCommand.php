<?php

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommand extends Command
{
    protected static $defaultName = 'migrate';

    protected function configure()
    {
        $this->setDescription('Run all pending migrations.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("<info>Running migrations...</info>");

        $migrationFiles = glob(__DIR__ . '/../database/*.php');
        if (empty($migrationFiles)) {
            $output->writeln("<info>No migrations found to run.</info>");
            return Command::SUCCESS;
        }

        foreach ($migrationFiles as $migrationFile) {
            include_once $migrationFile;
            $className = $this->getClassNameFromFile($migrationFile);

            if (class_exists($className)) {
                $migrationInstance = new $className();
                $tableName = $migrationInstance->getTableName(); // Adjust this line to fit your actual migration structure

                if ($this->checkIfTableExists($tableName)) {
                    $output->writeln("<info>Table {$tableName} already exists. Skipping migration: {$className}</info>");
                    continue; // Skip this migration
                }

                if (method_exists($migrationInstance, 'up')) {
                    $migrationInstance->up(); // Run the migration
                    $output->writeln("<info>Ran migration: {$className}</info>");
                }
            }
        }

        return Command::SUCCESS;
    }

    protected function checkIfTableExists($tableName)
    {
        return Capsule::schema()->hasTable($tableName);
    }

    protected function getClassNameFromFile($file)
    {
        $className = basename($file, '.php');
        $className = str_replace('_', '', ucwords($className, '_')); // CamelCase the class name
        return $className;
    }
}
