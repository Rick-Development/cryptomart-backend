<?php

/**
 * Script to fix module namespaces after creation
 * Usage: php fix-module-namespaces.php <ModuleName1> <ModuleName2> ...
 * Or: php fix-module-namespaces.php all (to fix all modules)
 */

$modules = [];

if (isset($argv[1])) {
    if ($argv[1] === 'all') {
        // Get all modules from Modules directory
        $modulesDir = __DIR__ . '/Modules';
        if (is_dir($modulesDir)) {
            $dirs = scandir($modulesDir);
            foreach ($dirs as $dir) {
                if ($dir !== '.' && $dir !== '..' && is_dir($modulesDir . '/' . $dir)) {
                    $modules[] = $dir;
                }
            }
        }
    } else {
        // Fix specific modules
        for ($i = 1; $i < count($argv); $i++) {
            $modules[] = $argv[$i];
        }
    }
} else {
    echo "Usage: php fix-module-namespaces.php <ModuleName1> [ModuleName2] ...\n";
    echo "   Or: php fix-module-namespaces.php all\n";
    exit(1);
}

foreach ($modules as $moduleName) {
    $modulePath = __DIR__ . "/Modules/{$moduleName}";
    $moduleLower = strtolower($moduleName);

    if (!is_dir($modulePath)) {
        echo "Module {$moduleName} not found at {$modulePath}\n";
        continue;
    }

    echo "\n=== Fixing {$moduleName} module ===\n";

    // Fix module.json
    $moduleJsonPath = "{$modulePath}/module.json";
    if (file_exists($moduleJsonPath)) {
        $content = file_get_contents($moduleJsonPath);
        $originalContent = $content;
        
        // Fix provider namespace - need to escape properly for JSON
        $content = preg_replace(
            "/\"Modules\\\\\\\\{$moduleName}\\\\\\\\Providers\\\\\\\\{$moduleName}ServiceProvider\"/",
            "\"Modules\\\\\\\\{$moduleName}\\\\\\\\app\\\\\\\\Providers\\\\\\\\{$moduleName}ServiceProvider\"",
            $content
        );
        
        if ($content !== $originalContent) {
            file_put_contents($moduleJsonPath, $content);
            echo "✓ Fixed module.json\n";
        }
    }

    // Fix service providers
    $files = [
        "{$modulePath}/app/Providers/{$moduleName}ServiceProvider.php",
        "{$modulePath}/app/Providers/EventServiceProvider.php",
        "{$modulePath}/app/Providers/RouteServiceProvider.php",
    ];

    foreach ($files as $file) {
        if (file_exists($file)) {
            $content = file_get_contents($file);
            $originalContent = $content;
            
            // Fix namespace
            $content = preg_replace(
                "/namespace Modules\\\\{$moduleName}\\\\Providers;/",
                "namespace Modules\\{$moduleName}\\app\\Providers;",
                $content
            );
            
            // Fix service provider register method (for main service provider only)
            if (strpos($file, 'ServiceProvider.php') !== false && 
                strpos($file, 'EventServiceProvider') === false && 
                strpos($file, 'RouteServiceProvider') === false) {
                
                // Fix EventServiceProvider reference
                $content = preg_replace(
                    "/\\\$this->app->register\\(EventServiceProvider::class\\);/",
                    "\$this->app->register(\\Modules\\{$moduleName}\\app\\Providers\\EventServiceProvider::class);",
                    $content
                );
                
                // Fix RouteServiceProvider reference
                $content = preg_replace(
                    "/\\\$this->app->register\\(RouteServiceProvider::class\\);/",
                    "\$this->app->register(\\Modules\\{$moduleName}\\app\\Providers\\RouteServiceProvider::class);",
                    $content
                );
            }
            
            if ($content !== $originalContent) {
                file_put_contents($file, $content);
                echo "✓ Fixed " . basename($file) . "\n";
            }
        }
    }

    // Fix controller
    $controllerFile = "{$modulePath}/app/Http/Controllers/{$moduleName}Controller.php";
    if (file_exists($controllerFile)) {
        $content = file_get_contents($controllerFile);
        $originalContent = $content;
        
        $content = preg_replace(
            "/namespace Modules\\\\{$moduleName}\\\\Http\\\\Controllers;/",
            "namespace Modules\\{$moduleName}\\app\\Http\\Controllers;",
            $content
        );
        
        if ($content !== $originalContent) {
            file_put_contents($controllerFile, $content);
            echo "✓ Fixed " . basename($controllerFile) . "\n";
        }
    }
}

echo "\n=== Done! ===\n";

