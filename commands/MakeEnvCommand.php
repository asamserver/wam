<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Support\Facades\File;

class MakeEnvCommand extends Command
{
    protected static $defaultName = 'make:env';

    protected function configure()
    {
        $this
            ->setDescription('Create a .env file from .env.example if it does not already exist.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $currentDir = getcwd(); 
        $envPath = $currentDir . DIRECTORY_SEPARATOR . '.env';
        $envExamplePath = $currentDir . DIRECTORY_SEPARATOR . '.env.example';
        if (file_exists($envPath)) {
            $output->writeln('<info>.env file already exists. No action taken.</info>');
            return Command::SUCCESS;
        }
        if (!file_exists($envExamplePath)) {
            $output->writeln('<error>.env.example file does not exist. Unable to create .env file.</error>');
            return Command::FAILURE;
        }
        try {
            if (copy($envExamplePath, $envPath)) {
                $output->writeln('<info>.env file successfully created from .env.example.</info>');
                return Command::SUCCESS;
            } else {
                $output->writeln('<error>Failed to create .env file. Please check file permissions.</error>');
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $output->writeln('<error>An error occurred: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}