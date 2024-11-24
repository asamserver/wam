<?php
// commands/ControllerCommand.php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ControllerCommand extends Command
{
    protected static $defaultName = 'make:controller';

    protected function configure()
    {
        $this
            ->setDescription('Create a new controller')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the controller (e.g., Dashboard)')
            // No need for 'type' argument, type is inferred from the directory structure
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        
        // Extract the controller type from the directory structure, e.g., 'admin' or 'client'
        $currentDir = getcwd();
        $controllerDir = $currentDir . '/src/Controllers'; // Default directory for 'admin'

        // Ensure the target controller directory exists (Create nested directories)
        if (!is_dir($controllerDir)) {
            mkdir($controllerDir, 0777, true); // Ensure all directories in the path are created
        }

        // Controller filename and path
        $controllerName = ucfirst($name);
        $controllerPath = $controllerDir . '/' . $controllerName . '.php';
        
        // Load and modify the stub
        $stub = file_get_contents(__DIR__ . '/controller.stub');
        
        // Replace placeholders in the stub
        $stub = str_replace(
            ['{{name}}'],
            [$controllerName],
            $stub
        );

        // Write the controller to the appropriate file
        if (file_put_contents($controllerPath, $stub)) {
            $output->writeln("<info>Created model: $controllerPath</info>");
        } else {
            $output->writeln("<error>Failed to create model file. Please check permissions.</error>");
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}

