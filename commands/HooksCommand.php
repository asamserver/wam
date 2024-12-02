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
            ->addOption('usejob', null, InputOption::VALUE_OPTIONAL, 'Enable Redis for this hook', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $addonName = basename(getcwd());
        $hookName = $input->getArgument('name');
        $useJob = $input->getOption('usejob') !== false;

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
            '{{useJob}}'
        ], [
            $addonName,
            $hookName,
            $useJob ? 'true' : 'false'
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
        $hooksRegistrationPath = getcwd() . '/hooks.php';
        $hooksRegistrationStubPath = __DIR__ . '/stubs/hooks-registration.stub';

        // Check if Hooks.php already exists
        if (!file_exists($hooksRegistrationPath)) {
            if (!file_exists($hooksRegistrationStubPath)) {
                $output->writeln("<error>Hooks registration stub not found!</error>");
                return;
            }

            // Create Hooks.php using the stub
            $hooksRegistrationStub = file_get_contents($hooksRegistrationStubPath);
            $hooksRegistrationStub = str_replace('{{addonName}}', $addonName, $hooksRegistrationStub);

            if (!file_put_contents($hooksRegistrationPath, $hooksRegistrationStub)) {
                $output->writeln("<error>Failed to create Hooks.php. Check permissions.</error>");
                return;
            }
            $output->writeln("<info>Created Hooks.php from stub: $hooksRegistrationPath</info>");
        }

        // Read existing Hooks.php content
        $hooksContent = file_get_contents($hooksRegistrationPath);

        // Prepare new hook registration logic
        $newHookUseStatement = "use WHMCS\\Module\\Addon\\{$addonName}\\app\\Hooks\\{$hookName}Hook;";
        $newHookRegistration = <<<EOL
        add_hook('{$hookName}', 1, function(\$params) {
            \$hookInstance = new {$hookName}Hook();
            \$hookInstance->handle(\$params);
        });
EOL;

        // Add `use` statement if not already present
        if (strpos($hooksContent, $newHookUseStatement) === false) {
            $hooksContent = preg_replace(
                '/(require_once __DIR__ . \'\/vendor\/autoload.php\';\n)/',
                "$1$newHookUseStatement\n",
                $hooksContent
            );
        }

        // Add hook registration logic if not already present
        if (strpos($hooksContent, $newHookRegistration) === false) {
            $hooksContent = preg_replace(
                '/(\/\/ Register hooks here\n)/',
                "$1$newHookRegistration\n",
                $hooksContent
            );
        }

        // Write back to Hooks.php
        if (file_put_contents($hooksRegistrationPath, $hooksContent)) {
            $output->writeln("<info>Updated Hooks.php with new hook: $hookName</info>");
        } else {
            $output->writeln("<error>Failed to update Hooks.php. Check permissions.</error>");
        }
    }
}
