<?php

declare(strict_types=1);

if (getenv('VERCEL')) {
    $temporaryPath = PHP_OS_FAMILY === 'Windows'
        ? sys_get_temp_dir().'/freshcart'
        : '/tmp';
    $storagePath = $temporaryPath.'/storage';
    $cachePath = $temporaryPath.'/bootstrap/cache';

    $directories = [
        $storagePath.'/framework/cache/data',
        $storagePath.'/framework/sessions',
        $storagePath.'/framework/views',
        $storagePath.'/logs',
    ];

    if (PHP_OS_FAMILY !== 'Windows') {
        $directories[] = $cachePath;
    }

    foreach ($directories as $directory) {
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException("Unable to create serverless directory: {$directory}");
        }
    }

    $environmentPaths = [
        'LARAVEL_STORAGE_PATH' => $storagePath,
        'VIEW_COMPILED_PATH' => $storagePath.'/framework/views',
    ];

    if (PHP_OS_FAMILY !== 'Windows') {
        $environmentPaths += [
            'APP_CONFIG_CACHE' => $cachePath.'/config.php',
            'APP_EVENTS_CACHE' => $cachePath.'/events.php',
            'APP_PACKAGES_CACHE' => $cachePath.'/packages.php',
            'APP_ROUTES_CACHE' => $cachePath.'/routes.php',
            'APP_SERVICES_CACHE' => $cachePath.'/services.php',
        ];
    }

    foreach ($environmentPaths as $key => $value) {
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

require __DIR__.'/../public/index.php';
