<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class HooksCommand extends Command
{
    protected static $defaultName = 'make:hook';

    protected function configure()
    {
        $this
            ->setDescription('Create a new WHMCS hook')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the hook (e.g., ClientLogin)')
            ->addOption('redis', null, InputOption::VALUE_OPTIONAL, 'Enable Redis for this hook', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $addonName = basename(getcwd());
        $hookName = $input->getArgument('name');
        $useRedis = $input->getOption('redis') !== false;

        // Ensure Hooks directory exists
        $hooksDir = getcwd() . '/app/Hooks';
        if (!is_dir($hooksDir)) {
            mkdir($hooksDir, 0777, true);
            $output->writeln("<info>Created Hooks directory: $hooksDir</info>");
        }

        // Ensure HooksManager exists
        $this->ensureHooksManager($output, $addonName);

        // Create Hook file
        $hookFilePath = $hooksDir . '/' . $hookName . 'Hook.php';
        $stubPath = __DIR__ . '/stubs/hook.stub';

        if (!file_exists($stubPath)) {
            $output->writeln("<error>Hook stub file not found at: $stubPath</error>");
            return Command::FAILURE;
        }

        $hookStub = file_get_contents($stubPath);
        $hookStub = str_replace([
            '{{addonName}}', 
            '{{hookName}}', 
            '{{useRedis}}'
        ], [
            $addonName, 
            $hookName, 
            $useRedis ? 'true' : 'false'
        ], $hookStub);

        if (file_put_contents($hookFilePath, $hookStub)) {
            $output->writeln("<info>Created hook file: $hookFilePath</info>");
        } else {
            $output->writeln("<error>Failed to create hook file. Please check permissions.</error>");
            return Command::FAILURE;
        }

        // Update Hooks.php for registration
        $this->updateHooksRegistration($output, $addonName, $hookName);

        return Command::SUCCESS;
    }

    protected function ensureHooksManager(OutputInterface $output, string $addonName): void
    {
        $hooksManagerPath = getcwd() . '/app/Hooks/HooksManager.php';
        $hooksManagerStubPath = __DIR__ . '/stubs/hooks-manager.stub';

        if (!file_exists($hooksManagerPath)) {
            if (!file_exists($hooksManagerStubPath)) {
                $output->writeln("<error>Hooks manager stub not found!</error>");
                return;
            }

            $hooksManagerStub = file_get_contents($hooksManagerStubPath);
            $hooksManagerStub = str_replace('{{addonName}}', $addonName, $hooksManagerStub);

            if (file_put_contents($hooksManagerPath, $hooksManagerStub)) {
                $output->writeln("<info>Created HooksManager: $hooksManagerPath</info>");
            } else {
                $output->writeln("<error>Failed to create HooksManager. Check permissions.</error>");
            }
        }
    }

    protected function updateHooksRegistration(OutputInterface $output, string $addonName, string $hookName): void
    {
        $hooksRegistrationPath = getcwd() . '/Hooks.php';
        
        // Create Hooks.php if it doesn't exist
        if (!file_exists($hooksRegistrationPath)) {
            $hooksRegistrationStubPath = __DIR__ . '/stubs/hooks-registration.stub';
            
            if (!file_exists($hooksRegistrationStubPath)) {
                $output->writeln("<error>Hooks registration stub not found!</error>");
                return;
            }

            $hooksRegistrationStub = file_get_contents($hooksRegistrationStubPath);
            $hooksRegistrationStub = str_replace('{{addonName}}', $addonName, $hooksRegistrationStub);

            if (file_put_contents($hooksRegistrationPath, $hooksRegistrationStub)) {
                $output->writeln("<info>Created Hooks.php: $hooksRegistrationPath</info>");
            } else {
                $output->writeln("<error>Failed to create Hooks.php. Check permissions.</error>");
            }
        }

        // Add hook to registration logic
        $hooksContent = file_get_contents($hooksRegistrationPath);
        $hookRegistrationLine = "        add_hook('" . ucfirst($hookName) . "', 1, [new Hooks\\" . $hookName . "Hook(), 'handle']);";
        
        // Only add if not already exists
        if (strpos($hooksContent, $hookRegistrationLine) === false) {
            $hooksContent = str_replace(
                "        // Register hooks here", 
                "        // Register hooks here\n" . $hookRegistrationLine, 
                $hooksContent
            );
            
            file_put_contents($hooksRegistrationPath, $hooksContent);
            $output->writeln("<info>Registered hook: $hookName</info>");
        }
    }
}