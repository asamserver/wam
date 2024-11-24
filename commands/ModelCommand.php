<?php

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Database\Schema\Blueprint;

class ModelCommand extends Command
{
    protected static $defaultName = 'make:model';

    protected function configure()
    {
        $this
            ->setDescription('Generate a model and ensure its corresponding table exists in the database.')
            ->addArgument('modelName', InputArgument::REQUIRED, 'The name of the model');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Get the addon name from the parent directory
        $addonName  = basename(getcwd());

        $modelName = $input->getArgument('modelName');
        $tableName = strtolower($modelName).'s'; // Automatically set tableName to lowercase modelName
        $currentDir = getcwd();
        $modelDir = $currentDir . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Models';

        // Ensure the models directory exists
        if (!is_dir($modelDir)) {
            if (!mkdir($modelDir, 0777, true)) {
                $output->writeln("<error>Failed to create models directory. Please check permissions.</error>");
                return Command::FAILURE;
            }
        }

        // Path to the model file
        $modelFilePath = $modelDir . DIRECTORY_SEPARATOR . $modelName . '.php';

        // Load and process the stub file
        $stubPath = __DIR__ . '/model.stub';  // Path to the stub file
        if (!file_exists($stubPath)) {
            $output->writeln("<error>Model stub file not found: $stubPath</error>");
            return Command::FAILURE;
        }

        // Read the stub and replace placeholders
        $stubContent = file_get_contents($stubPath);
        $modelStub = str_replace(
            ['{{addonName}}', '{{modelName}}', '{{tableName}}'],
            [$addonName, $modelName, $tableName],
            $stubContent
        );

        // Write the model file
        if (file_put_contents($modelFilePath, $modelStub)) {
            $output->writeln("<info>Created model: $modelFilePath</info>");
        } else {
            $output->writeln("<error>Failed to create model file. Please check permissions.</error>");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
