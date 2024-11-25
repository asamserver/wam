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
        $currentDir = getcwd(); 
        $appDir = $currentDir . DIRECTORY_SEPARATOR . 'app';
        if (!is_dir($appDir)) {
            if (!mkdir($appDir, 0777, true)) {
                $output->writeln("<error>Failed to create app directory. Please check permissions.</error>");
                return Command::FAILURE;
            }
            $output->writeln("<info>Created app directory: $appDir</info>");
        }
        $addonFilePath = $currentDir . DIRECTORY_SEPARATOR . $addonName . '.php';
        $stubFile = __DIR__ . '/stubs/addon.stub';
        if (!file_exists($stubFile)) {
            $output->writeln("<error>Stub file not found at: $stubFile</error>");
            return Command::FAILURE;
        }
        $addonFileContent = file_get_contents($stubFile);
        $addonFileContent = str_replace('$addonName', $addonName, $addonFileContent);
        if (file_put_contents($addonFilePath, $addonFileContent)) {
            $output->writeln("<info>Created addon file: $addonFilePath</info>");
        } else {
            $output->writeln("<error>Failed to create $addonFilePath. Please check permissions.</error>");
            return Command::FAILURE;
        }
        $applicationFilePath = $appDir . DIRECTORY_SEPARATOR . 'Application.php';
        $applicationStub = __DIR__ . '/stubs/application.stub';
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
        $helperDir = $currentDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Helper';
        if (!is_dir($helperDir)) {
            if (!mkdir($helperDir, 0777, true)) {
                $output->writeln("<error>Failed to create Helper directory. Please check permissions.</error>");
                return Command::FAILURE;
            }
            $output->writeln("<info>Created Helper directory: $helperDir</info>");
        }

        $helperFilePath = $helperDir . DIRECTORY_SEPARATOR . 'Helper.php';
        $helperStub = __DIR__ . '/stubs/helper.stub';

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

        $addonName = $input->getArgument('addonName');
        $currentDir = getcwd();
        $adminDispatcherDir = $currentDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Dispatcher';
        if (!is_dir($adminDispatcherDir)) {
            if (!mkdir($adminDispatcherDir, 0777, true)) {
                $output->writeln("<error>Failed to create adminDispatcher directory. Please check permissions.</error>");
                return Command::FAILURE;
            }
            $output->writeln("<info>Created adminDispatcher directory: $adminDispatcherDir</info>");
        }
        $adminDispatcherStub = __DIR__ . '/stubs/adminDispatcher.stub';
        if (!file_exists($adminDispatcherStub)) {
            $output->writeln("<error>Stub file not found at: $adminDispatcherStub</error>");
            return Command::FAILURE;
        }
        $adminDispatcherFileContent = file_get_contents($adminDispatcherStub);
        $adminDispatcherFileContent = str_replace('$addonName', $addonName, $adminDispatcherFileContent);
        $adminDispatcherFilePath = $adminDispatcherDir . DIRECTORY_SEPARATOR . 'AdminDispatcher.php';
        if (file_put_contents($adminDispatcherFilePath, $adminDispatcherFileContent)) {
            $output->writeln("<info>Created AdminDispatcher file: $adminDispatcherFilePath</info>");
        } else {
            $output->writeln("<error>Failed to create $adminDispatcherFilePath. Please check permissions.</error>");
            return Command::FAILURE;
        }


        // Creat Client Dispatcher 

        $clientDispatcherStub = __DIR__ . '/stubs/clientDispatcher.stub';

        if (!file_exists($clientDispatcherStub)) {
            $output->writeln("<error>Stub file not found at: $clientDispatcherStub</error>");
            return Command::FAILURE;
        }

        $clientDispatcherFileContent = file_get_contents($clientDispatcherStub);
        $clientDispatcherFileContent = str_replace('$addonName', $addonName, $clientDispatcherFileContent);
        $clientDispatcherFilePath = $adminDispatcherDir . DIRECTORY_SEPARATOR . 'ClientDispatcher.php';
        if (file_put_contents($clientDispatcherFilePath, $clientDispatcherFileContent)) {
            $output->writeln("<info>Created AdminDispatcher file: $clientDispatcherFilePath</info>");
        } else {
            $output->writeln("<error>Failed to create $clientDispatcherFilePath. Please check permissions.</error>");
            return Command::FAILURE;
        }
        $addonName = $input->getArgument('addonName');
        $currentDir = getcwd();
        $routersatcherDir =  $currentDir . DIRECTORY_SEPARATOR . 'app';
        if (!is_dir($routersatcherDir)) {
            if (!mkdir($routersatcherDir, 0777, true)) {
                $output->writeln("<error>Failed to create adminDispatcher directory. Please check permissions.</error>");
                return Command::FAILURE;
            }
            $output->writeln("<info>Created adminDispatcher directory: $routersatcherDir</info>");
        }
        $RouterStub = __DIR__ . '/stubs/router.stub';

        if (!file_exists($RouterStub)) {
            $output->writeln("<error>Stub file not found at: $RouterStub</error>");
            return Command::FAILURE;
        }

        $routerContent = file_get_contents($RouterStub);
        $routerContent = str_replace('$addonName', $addonName, $routerContent);
        $routerFilePath = $routersatcherDir . DIRECTORY_SEPARATOR . 'Router.php';
        if (file_put_contents($routerFilePath, $routerContent)) {
            $output->writeln("<info>Created Router file: $routerFilePath</info>");
        } else {
            $output->writeln("<error>Failed to create $routerFilePath. Please check permissions.</error>");
            return Command::FAILURE;
        }





        // Ensure the directory exists, create it if necessary
        $BaseControllersDir = $currentDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Controllers';
        if (!is_dir($BaseControllersDir)) {
            if (!mkdir($BaseControllersDir, 0777, true)) {
                $output->writeln("<error>Failed to create BaseController directory. Please check permissions.</error>");
                return Command::FAILURE;
            }
            $output->writeln("<info>Created BaseController directory: $BaseControllersDir</info>");
        }
        $BaseControllerStub = __DIR__ . '/stubs/basecontroller.stub';
        if (!file_exists($BaseControllerStub)) {
            $output->writeln("<error>Stub file not found at: $BaseControllerStub</error>");
            return Command::FAILURE;
        }
        $BaseControllerFileContent = file_get_contents($BaseControllerStub);
        $BaseControllerFileContent = str_replace('$addonName', $addonName, $BaseControllerFileContent);
        $BaseControllerFilePath = $BaseControllersDir . DIRECTORY_SEPARATOR . 'BaseController.php';
        $writeResult = file_put_contents($BaseControllerFilePath, $BaseControllerFileContent);
        if ($writeResult === false) {
            $output->writeln("<error>Failed to create $BaseControllerFilePath. Please check permissions.</error>");
            return Command::FAILURE;
        } else {
            $output->writeln("<info>Created BaseController file: $BaseControllerFilePath</info>");
        }

        return Command::SUCCESS;
    }
}
