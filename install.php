#!/usr/bin/env php
<?php

declare(strict_types=1);

use BrowserDriverInstaller\Command\InstallCommand;
use BrowserDriverInstaller\Enum\BrowserName;
use BrowserDriverInstaller\Factory\BrowserFactory;
use BrowserDriverInstaller\Factory\BrowserVersionResolverFactory;
use BrowserDriverInstaller\Resolver\Version\Browser\GoogleChromeVersionResolver;
use Symfony\Component\Console\Application;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\NativeHttpClient;

require __DIR__ . '/vendor/autoload.php';

$application = new Application();

$browserVersionResolverFactory = new BrowserVersionResolverFactory();
$browserVersionResolverFactory->register(new GoogleChromeVersionResolver());

$browserFactory = new BrowserFactory($browserVersionResolverFactory);
$browserFactory->register(BrowserName::GOOGLE_CHROME());
$browserFactory->register(BrowserName::CHROMIUM());
$browserFactory->register(BrowserName::FIREFOX());

$application->add(
    new InstallCommand(
        'driver',
        new NativeHttpClient(),
        new Filesystem(),
        new ZipArchive(),
        $browserFactory
    )
);

$application->setDefaultCommand('driver');
$application->run();
