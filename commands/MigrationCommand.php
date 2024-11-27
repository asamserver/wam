<?php

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationCommand extends Command
{
    protected static $defaultName = 'make:migration';

    protected function configure()
    {
        $this
            ->setDescription('Create a new migration file for the specified table.')
            ->addArgument('tableName', InputArgument::REQUIRED, 'The name of the table for the migration')
            ->addOption('migrate', null, InputOption::VALUE_NONE, 'Run migrations after creating the migration file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tableName = env('TABLE_PREFIX').$input->getArgument('tableName');
        $currentDir = getcwd(); 
        $databaseDir = $currentDir . DIRECTORY_SEPARATOR . 'database';
        if (!is_dir($databaseDir)) {
            if (!mkdir($databaseDir, 0777, true)) {
                $output->writeln("<error>Failed to create database directory. Please check permissions.</error>");
                return Command::FAILURE;
            }
            $output->writeln("<info>Created database directory: $databaseDir</info>");
        }

        if ($this->checkIfMigrationExists($tableName, $output)) {
            return Command::SUCCESS; // Migration already exists, so skip creation
        }
        $timestamp = date('Y_m_d_His');
        $migrationFileName = "create_{$tableName}_table.php";
        $migrationFilePath = $databaseDir . DIRECTORY_SEPARATOR . $timestamp . '_' . $migrationFileName;
        $stubPath = __DIR__ . '/stubs/migration.stub';
        if (!file_exists($stubPath)) {
            $output->writeln("<error>Migration stub file not found at: $stubPath</error>");
            return Command::FAILURE;
        }

        $stub = file_get_contents($stubPath);
        $stub = str_replace('{{tableName}}', $tableName, $stub);
        if (file_put_contents($migrationFilePath, $stub)) {
            $output->writeln("<info>Created migration file: $migrationFilePath</info>");
        } else {
            $output->writeln("<error>Failed to create migration file. Please check permissions.</error>");
            return Command::FAILURE;
        }
        $runMigrations = $input->getOption('migrate');
        if ($runMigrations) {
            $this->runMigrate($output);
        }

        return Command::SUCCESS;
    }

    protected function checkIfMigrationExists($tableName, OutputInterface $output)
    {
        $currentDir = getcwd();
        $databaseDir = $currentDir . DIRECTORY_SEPARATOR . 'database';
        $migrationFiles = glob($databaseDir . DIRECTORY_SEPARATOR . "*_create_{$tableName}_table.php");

        if (!empty($migrationFiles)) {
            $output->writeln("<info>Migration for table '{$tableName}' already exists. Skipping creation.</info>");
            return true; 
        }
        return false;
    }

    protected function runMigrate(OutputInterface $output)
    {
        $output->writeln("<info>Running migrations...</info>");

        $migrationFiles = glob(__DIR__.'/../database/*.php');
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
