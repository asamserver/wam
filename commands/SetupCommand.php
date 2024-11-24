<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetupCommand extends Command
{
    protected static $defaultName = 'make:addon';

    protected function configure()
    {
        $this
            ->setDescription('Generate a new WHMCS addon module.')
            ->addArgument('addonName', InputArgument::REQUIRED, 'The name of the addon');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {


        $envExamplePath = __DIR__ . '/../.env.example';
        $envPath = __DIR__ . '/../.env';

        if (!file_exists($envPath)) {
            if (file_exists($envExamplePath)) {
                if (copy($envExamplePath, $envPath)) {
                    $output->writeln("<info>.env file has been successfully created from .env.example</info>");
                } else {
                    $output->writeln("<error>Failed to copy .env.example to .env. Please check file permissions.</error>");
                    return Command::FAILURE;
                }
            } else {
                $output->writeln("<error>.env.example file does not exist. Please create it before proceeding.</error>");
                return Command::FAILURE;
            }
        } else {
            $output->writeln("<info>.env file already exists. Skipping creation.</info>");
        }

        $addonName = $input->getArgument('addonName');
        $currentDir = getcwd(); // Current working directory

        // Ensure the src directory exists
        $srcDir = $currentDir . DIRECTORY_SEPARATOR . 'src';
        if (!is_dir($srcDir)) {
            if (!mkdir($srcDir, 0777, true)) {
                $output->writeln("<error>Failed to create src directory. Please check permissions.</error>");
                return Command::FAILURE;
            }
            $output->writeln("<info>Created src directory: $srcDir</info>");
        }

        // Path to the new addon file
        $addonFilePath = $currentDir . DIRECTORY_SEPARATOR . $addonName . '.php';

        // Path to the stub file (template)
        $stubFile = __DIR__ . '/addon.stub';
        if (!file_exists($stubFile)) {
            $output->writeln("<error>Stub file not found at: $stubFile</error>");
            return Command::FAILURE;
        }

        // Read stub content and replace placeholders
        $addonFileContent = file_get_contents($stubFile);
        $addonFileContent = str_replace('$addonName', $addonName, $addonFileContent);

        // Create the addon file
        if (file_put_contents($addonFilePath, $addonFileContent)) {
            $output->writeln("<info>Created addon file: $addonFilePath</info>");
        } else {
            $output->writeln("<error>Failed to create $addonFilePath. Please check permissions.</error>");
            return Command::FAILURE;
        }

        // Now create the Application.php file
        $applicationFilePath = $srcDir . DIRECTORY_SEPARATOR . 'Application.php';
        $applicationStub = __DIR__ . '/application.stub';

        if (!file_exists($applicationStub)) {
            $output->writeln("<error>Stub file not found at: $applicationStub</error>");
            return Command::FAILURE;
        }

        $applicationFileContent = file_get_contents($applicationStub);
        $applicationFileContent = str_replace('$addonName', $addonName, $applicationFileContent);

        if (file_put_contents($applicationFilePath, $applicationFileContent)) {
            $output->writeln("<info>Created Application file: $applicationFilePath</info>");
        } else {
            $output->writeln("<error>Failed to create $applicationFilePath. Please check permissions.</error>");
            return Command::FAILURE;
        }


        // Now create the Helper.php file
        $helperDir = $currentDir . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Helper';
        if (!is_dir($helperDir)) {
            if (!mkdir($helperDir, 0777, true)) {
                $output->writeln("<error>Failed to create Helper directory. Please check permissions.</error>");
                return Command::FAILURE;
            }
            $output->writeln("<info>Created Helper directory: $helperDir</info>");
        }

        $helperFilePath = $helperDir . DIRECTORY_SEPARATOR . 'Helper.php';
        $helperStub = __DIR__ . '/helper.stub';

        if (!file_exists($helperStub)) {
            $output->writeln("<error>Stub file not found at: $helperStub</error>");
            return Command::FAILURE;
        }

        $helperFileContent = file_get_contents($helperStub);
        $helperFileContent = str_replace('${addonName}', $addonName, $helperFileContent);

        if (file_put_contents($helperFilePath, $helperFileContent)) {
            $output->writeln("<info>Created helper file: $helperFilePath</info>");
        } else {
            $output->writeln("<error>Failed to create $helperFilePath. Please check permissions.</error>");
            return Command::FAILURE;
        }


        // Now create the AdminDispatcher.php file
        // Get the addon name from the argument
        $addonName = $input->getArgument('addonName');

        // Get the current working directory
        $currentDir = getcwd();

        // Set the directory where the AdminDispatcher.php file will be created
        $adminDispatcherDir = $currentDir . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Dispatcher';

        // Ensure the directory exists, create it if necessary
        if (!is_dir($adminDispatcherDir)) {
            if (!mkdir($adminDispatcherDir, 0777, true)) {
                $output->writeln("<error>Failed to create adminDispatcher directory. Please check permissions.</error>");
                return Command::FAILURE;
            }
            $output->writeln("<info>Created adminDispatcher directory: $adminDispatcherDir</info>");
        }

        // Path to the stub file
        $adminDispatcherStub = __DIR__ . '/adminDispatcher.stub';

        if (!file_exists($adminDispatcherStub)) {
            $output->writeln("<error>Stub file not found at: $adminDispatcherStub</error>");
            return Command::FAILURE;
        }

        // Read the stub content
        $adminDispatcherFileContent = file_get_contents($adminDispatcherStub);

        // Replace the placeholder $addonName with the actual addon name
        $adminDispatcherFileContent = str_replace('$addonName', $addonName, $adminDispatcherFileContent);

        // Set the path to the AdminDispatcher.php file
        $adminDispatcherFilePath = $adminDispatcherDir . DIRECTORY_SEPARATOR . 'AdminDispatcher.php';

        // Create the AdminDispatcher.php file
        if (file_put_contents($adminDispatcherFilePath, $adminDispatcherFileContent)) {
            $output->writeln("<info>Created AdminDispatcher file: $adminDispatcherFilePath</info>");
        } else {
            $output->writeln("<error>Failed to create $adminDispatcherFilePath. Please check permissions.</error>");
            return Command::FAILURE;
        }


        // Creat Client Dispatcher 

        $clientDispatcherStub = __DIR__ . '/clientDispatcher.stub';

        if (!file_exists($clientDispatcherStub)) {
            $output->writeln("<error>Stub file not found at: $clientDispatcherStub</error>");
            return Command::FAILURE;
        }
        // Read the stub content
        $clientDispatcherFileContent = file_get_contents($clientDispatcherStub);

        // Replace the placeholder $addonName with the actual addon name
        $clientDispatcherFileContent = str_replace('$addonName', $addonName, $clientDispatcherFileContent);

        // Set the path to the AdminDispatcher.php file
        $clientDispatcherFilePath = $adminDispatcherDir . DIRECTORY_SEPARATOR . 'ClientDispatcher.php';

        // Create the AdminDispatcher.php file
        if (file_put_contents($clientDispatcherFilePath, $clientDispatcherFileContent)) {
            $output->writeln("<info>Created AdminDispatcher file: $clientDispatcherFilePath</info>");
        } else {
            $output->writeln("<error>Failed to create $clientDispatcherFilePath. Please check permissions.</error>");
            return Command::FAILURE;
        }




         // Now create the Router.php file
        $addonName = $input->getArgument('addonName');

        // Get the current working directory
        $currentDir = getcwd();

        // Set the directory where the AdminDispatcher.php file will be created
        $routersatcherDir = $currentDir . DIRECTORY_SEPARATOR . 'routes';

        // Ensure the directory exists, create it if necessary
        if (!is_dir($routersatcherDir)) {
            if (!mkdir($routersatcherDir, 0777, true)) {
                $output->writeln("<error>Failed to create adminDispatcher directory. Please check permissions.</error>");
                return Command::FAILURE;
            }
            $output->writeln("<info>Created adminDispatcher directory: $routersatcherDir</info>");
        }

        // Path to the stub file
        $RouterStub = __DIR__ . '/router.stub';

        if (!file_exists($RouterStub)) {
            $output->writeln("<error>Stub file not found at: $RouterStub</error>");
            return Command::FAILURE;
        }

        // Read the stub content
        $routerContent = file_get_contents($RouterStub);

        // Replace the placeholder $addonName with the actual addon name
        $routerContent = str_replace('$addonName', $addonName, $routerContent);

        // Set the path to the AdminDispatcher.php file
        $routerFilePath = $routersatcherDir . DIRECTORY_SEPARATOR . 'Router.php';

        // Create the AdminDispatcher.php file
        if (file_put_contents($routerFilePath, $routerContent)) {
            $output->writeln("<info>Created Router file: $routerFilePath</info>");
        } else {
            $output->writeln("<error>Failed to create $routerFilePath. Please check permissions.</error>");
            return Command::FAILURE;
        }




        return Command::SUCCESS;
    }
}
