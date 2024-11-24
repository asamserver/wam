<?php

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
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the controller (e.g., Admin/DashboardController)')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        
        // Extract the controller type from the directory structure, e.g., 'admin' or 'client'
        $currentDir = getcwd();
        $controllerDir = $currentDir . '/app/Controllers'; // Default directory for 'admin'

        // Debug print to check the incoming name argument
        $output->writeln("<info>Received controller name: $name</info>");

        // Split the path into parts (e.g., Admin/DashboardController -> ['Admin', 'DashboardController'])
        $pathParts = explode('/', $name);
        
        // Determine the namespace and directory structure
        $namespace = 'WHMCS\\Module\\Addon\\' . basename(getcwd()) . '\\app\\Controllers\\' . implode('\\', array_slice($pathParts, 0, -1));
        $controllerName = end($pathParts); // The last part is the controller name
        
        // Controller directory path
        $controllerDirPath = $controllerDir . '/' . implode('/', array_slice($pathParts, 0, -1));
        
        // Debug print for the target controller directory path
        $output->writeln("<info>Controller directory path: $controllerDirPath</info>");

        // Ensure the target controller directory exists (Create nested directories)
        if (!is_dir($controllerDirPath)) {
            mkdir($controllerDirPath, 0777, true); // Create any necessary subdirectories
            $output->writeln("<info>Created directory: $controllerDirPath</info>");
        }

        // Controller filename and path
        $controllerPath = $controllerDirPath . '/' . $controllerName . '.php';

        // Debug print for the controller path
        $output->writeln("<info>Controller path: $controllerPath</info>");

        // Load and modify the stub
        $stub = file_get_contents(__DIR__ . '/stubs/controller.stub');
        $addonName  = basename(getcwd());

        // Replace placeholders in the stub
        $stub = str_replace(
            ['{{addonName}}','{{controllerName}}','{{name}}'],
            [$addonName, $controllerName, $name],
            $stub
        );

        // Replace namespace with dynamic namespace
        $stub = str_replace('{{namespace}}', $namespace, $stub);

        // Write the controller to the appropriate file
        if (file_put_contents($controllerPath, $stub)) {
            $output->writeln("<info>Created controller: $controllerPath</info>");
        } else {
            $output->writeln("<error>Failed to create controller file. Please check permissions.</error>");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
