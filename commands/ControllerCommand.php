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
        
        $currentDir = getcwd();
        $controllerDir = $currentDir . '/app/Controllers'; // Default directory for 'admin'
        $output->writeln("<info>Received controller name: $name</info>");
        $pathParts = explode('/', $name);
        $namespace = 'WHMCS\\Module\\Addon\\' . basename(getcwd()) . '\\app\\Controllers\\' . implode('\\', array_slice($pathParts, 0, -1));
        $controllerName = end($pathParts); // The last part is the controller name
        $controllerDirPath = $controllerDir . '/' . implode('/', array_slice($pathParts, 0, -1));
        $output->writeln("<info>Controller directory path: $controllerDirPath</info>");
        if (!is_dir($controllerDirPath)) {
            mkdir($controllerDirPath, 0777, true); // Create any necessary subdirectories
            $output->writeln("<info>Created directory: $controllerDirPath</info>");
        }
        $controllerPath = $controllerDirPath . '/' . $controllerName . '.php';
        $output->writeln("<info>Controller path: $controllerPath</info>");
        $stub = file_get_contents(__DIR__ . '/stubs/controller.stub');
        $addonName  = basename(getcwd());
        $stub = str_replace(
            ['{{addonName}}','{{controllerName}}','{{name}}'],
            [$addonName, $controllerName, $name],
            $stub
        );
        $stub = str_replace('{{namespace}}', $namespace, $stub);
        if (file_put_contents($controllerPath, $stub)) {
            $output->writeln("<info>Created controller: $controllerPath</info>");
        } else {
            $output->writeln("<error>Failed to create controller file. Please check permissions.</error>");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
