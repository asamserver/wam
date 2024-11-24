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
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the controller (e.g., dashboard)')
            // No need for 'type' argument, type is inferred from the directory structure
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        
        // Extract the controller type from the directory structure, e.g., 'admin' or 'client'
        $currentDir = getcwd();
        $type = 'admin'; // Default to admin if no directory exists
        $controllerDir = $currentDir . '/src/Controllers'; // Default directory for 'admin'

        // Check if the controller should be placed in the 'client' folder
        if (is_dir($currentDir . '/src/Controllers')) {
            $type = 'client';
            $controllerDir = $currentDir . '/src/Controllers';
        }

        // Ensure the target controller directory exists
        if (!is_dir($controllerDir)) {
            mkdir($controllerDir, 0777, true);
        }

        $controllerName = ucfirst($name);
        $controllerPath = $controllerDir . '/' . $controllerName . '.php';
        
        $stub = file_get_contents(__DIR__ . '/controller.stub');
        
        // Replace the placeholders
        $stub = str_replace(
            ['{{name}}', '{{type}}'],
            [$controllerName, ucfirst($type)],
            $stub
        );

        // Write the controller to the appropriate file
        file_put_contents($controllerPath, $stub);
        $output->writeln("<info>Controller created successfully: {$controllerPath}</info>");

        return Command::SUCCESS;
    }
}
