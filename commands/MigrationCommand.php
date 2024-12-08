<?php

require __DIR__ . '/../vendor/autoload.php';

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
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        if(!env('DB_PREFIX') || env('DB_PREFIX') == ''){
            echo 'Please set DB_PREFIX variable in .env file';
            return Command::FAILURE;
        }
        $tableName = env('DB_PREFIX').'_' . $input->getArgument('tableName');
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
            return Command::SUCCESS;
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
        $className = $this->getClassNameFromTableName($tableName);
        $stub = str_replace(['{{className}}', '{{tableName}}'], [$className, $tableName], $stub);
        if (file_put_contents($migrationFilePath, $stub)) {
            $output->writeln("<info>Created migration file: $migrationFilePath</info>");
        } else {
            $output->writeln("<error>Failed to create migration file. Please check permissions.</error>");
            return Command::FAILURE;
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
    protected function getClassNameFromTableName($tableName)
    {
        $className = ucwords(str_replace('_', ' ', $tableName));
        return 'Create' . str_replace(' ', '', $className) . 'Table';
    }
}