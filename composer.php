<?php

// Path to the composer.json file
$composerJsonPath = __DIR__ . '/composer.json';

// Check if the composer.json file exists
if (file_exists($composerJsonPath)) {
    // Read the current contents of the composer.json file
    $composerJson = json_decode(file_get_contents($composerJsonPath), true);

    // Check if 'autoload' and 'psr-4' keys exist, otherwise initialize them
    if (!isset($composerJson['autoload'])) {
        $composerJson['autoload'] = [];
    }

    if (!isset($composerJson['autoload']['psr-4'])) {
        $composerJson['autoload']['psr-4'] = [];
    }

    $currentDirName = basename(getcwd());

    // Define the new PSR-4 autoload entry using the current directory name
    $newAutoloadEntry = "WHMCS\\Module\\Addon\\$currentDirName\\app\\";
    $autoloadValue = "app/";

    // Add the new entry to the 'psr-4' section
    $composerJson['autoload']['psr-4'][$newAutoloadEntry] = $autoloadValue;

    // Write the updated data back to the composer.json file
    if (file_put_contents($composerJsonPath, json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
        echo "composer.json updated successfully.\n";
    } else {
        echo "Failed to update composer.json.\n";
    }

    // Remove the PHP file after it has updated the composer.json
    unlink(__FILE__);
    echo "PHP script removed successfully.\n";

} else {
    echo "composer.json not found.\n";
}
