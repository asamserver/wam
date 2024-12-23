<?php

require __DIR__ . '/../vendor/autoload.php';

use WHMCS\Database\Capsule;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Database\Schema\Blueprint;

class WebCommand extends Command
{
    protected static $defaultName = 'make:web';

    protected function configure()
    {
        $this
            ->setDescription('Generate a web and ensure its corresponding table exists in the database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $addonName  = basename(getcwd());
        $currentDir = getcwd();
        $webDir = $currentDir . DIRECTORY_SEPARATOR . 'routes';

        if (!is_dir($webDir)) {
            if (!mkdir($webDir, 0777, true)) {
                $output->writeln("<error>Failed to create webs directory. Please check permissions.</error>");
                return Command::FAILURE;
            }
        }

        $webFilePath = $webDir . DIRECTORY_SEPARATOR . 'web.php';
        $stubPath = __DIR__ . '/stubs/web.stub'; 

        if (!file_exists($stubPath)) {
            $output->writeln("<error>web stub file not found: $stubPath</error>");
            return Command::FAILURE;
        }
        $stubContent = file_get_contents($stubPath);
        $webStub = str_replace('{{addonName}}', $addonName, $stubContent);
        if (file_put_contents($webFilePath, $webStub)) {
            $output->writeln("<info>Created web: $webFilePath</info>");
        } else {
            $output->writeln("<error>Failed to create web file. Please check permissions.</error>");
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}
