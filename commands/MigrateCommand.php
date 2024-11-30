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
                if (method_exists($migrationInstance, 'up')) {
                    $migrationInstance->up();
                    $output->writeln("<info>Ran migration: {$className}</info>");
                }
            }
        }

        return Command::SUCCESS;
    }

    protected function getClassNameFromFile($file)
    {
        // Get the base filename without path or extension
        $fileName = basename($file, '.php');
        
        // Remove the timestamp prefix (digits and underscores) more explicitly
        $classNameWithoutTimestamp = preg_replace('/^\d+_/', '', $fileName); // Remove any digits and the first underscore
    
        // Convert the class name to CamelCase (remove underscores and capitalize)
        $className = str_replace('_', '', ucwords($classNameWithoutTimestamp, '_'));
    
        // Ensure the first letter is capitalized correctly
        $className = ucfirst($className);
    
        // Remove the 'Create' prefix or any other unwanted characters from the start of the class name
        if (strpos($className, 'Create') === 0) {
            $className = substr($className, 6); // Remove 'Create' (6 characters)
        }

        $className = preg_replace('/[0-9]/', '', $className);
        return $className;
    }
    
}
