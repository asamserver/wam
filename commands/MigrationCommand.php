<?php

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Migrations\Migration;
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
        $tableName = $input->getArgument('tableName');
        $currentDir = getcwd(); // Current working directory

        // Ensure the 'database' folder exists
        $databaseDir = $currentDir . DIRECTORY_SEPARATOR . 'database';
        if (!is_dir($databaseDir)) {
            if (!mkdir($databaseDir, 0777, true)) {
                $output->writeln("<error>Failed to create database directory. Please check permissions.</error>");
                return Command::FAILURE;
            }
            $output->writeln("<info>Created database directory: $databaseDir</info>");
        }

        // Generate the migration file name (timestamp + table name)
        $timestamp = date('Y_m_d_His');
        $migrationFileName = "create_{$tableName}_table.php";
        $migrationFilePath = $databaseDir . DIRECTORY_SEPARATOR . $timestamp . '_' . $migrationFileName;

        // Load the stub file from the current directory
        $stubPath = __DIR__ . '/stubs/migration.stub';
        if (!file_exists($stubPath)) {
            $output->writeln("<error>Migration stub file not found at: $stubPath</error>");
            return Command::FAILURE;
        }

        $stub = file_get_contents($stubPath);

        // Replace the tableName placeholder in the stub with the actual table name
        $stub = str_replace('{{tableName}}', $tableName, $stub);

        // Write the migration file
        if (file_put_contents($migrationFilePath, $stub)) {
            $output->writeln("<info>Created migration file: $migrationFilePath</info>");
        } else {
            $output->writeln("<error>Failed to create migration file. Please check permissions.</error>");
            return Command::FAILURE;
        }

        // Optionally run migrations or migrate:fresh here
        $runMigrations = $input->getOption('migrate');
        if ($runMigrations) {
            $this->runMigrate($output);
        }

        return Command::SUCCESS;
    }

    // Method to run migrations
    protected function runMigrate(OutputInterface $output)
    {
        $output->writeln("<info>Running migrations...</info>");
        $output->writeln(shell_exec('php artisan migrate'));
    }
}
