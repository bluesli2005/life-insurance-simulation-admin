<?php

$installedPath = __DIR__.'/../vendor/composer/installed.json';
$manifestPath = __DIR__.'/../bootstrap/cache/packages.php';

$installed = json_decode(file_get_contents($installedPath), true);
$packages = isset($installed['packages']) ? $installed['packages'] : $installed;
$manifest = [];

foreach ($packages as $package) {
    if (!isset($package['name'])) {
        continue;
    }

    $configuration = $package['extra']['laravel'] ?? [];
    $manifest[$package['name']] = $configuration;

    foreach ($configuration['dont-discover'] ?? [] as $ignoredPackage) {
        unset($manifest[$ignoredPackage]);
    }
}

file_put_contents($manifestPath, "<?php\n\nreturn ".var_export($manifest, true).";\n");
