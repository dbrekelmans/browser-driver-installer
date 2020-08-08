#!/usr/bin/env php
<?php

declare(strict_types=1);

use BrowserDriverInstaller\InstallCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\NativeHttpClient;

require __DIR__ . '/vendor/autoload.php';

$application = new Application();

$application->add(
    new InstallCommand(
        'driver',
        new NativeHttpClient(),
        new Filesystem(),
        new ZipArchive()
    )
);

$application->run();
