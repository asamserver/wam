<?php

// Get the current directory name
$currentDirName = basename(getcwd());

// Run the command to create the addon using the current directory name
$command = "php asam make:addon $currentDirName";

// Execute the command
exec($command, $output, $return_var);

// Check if the command was successful
if ($return_var === 0) {
    echo "Addon creation command executed successfully.\n";
} else {
    echo "Failed to execute the command.\n";
    echo implode("\n", $output); // Output error details if command failed
}

// Self-delete the PHP script
unlink(__FILE__);

echo "This PHP file will now be deleted.\n";
?>
