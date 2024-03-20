# browser-driver-installer
This command-line tool helps you install browser drivers.
A common use-case is to install a browser driver to run your functional test suite.

## Why
While running automated testing tools in CI, you might currently install the latest version of your browser and
browser driver. These can become out-of-sync (for example: a new driver is released, but the matching browser is only released
a few days later).

This tool installs a driver for you that will always work with your installed browser.

## Installation
### Phive
Install with `phive install bdi` or `phive install dbrekelmans/browser-driver-installer`.

Run with `tools/bdi <command>`.

_See https://github.com/phar-io/phive for information about phive itself._ 

### Composer
Install with `composer require --dev dbrekelmans/bdi`.

Run with `vendor/bin/bdi <command>` or `vendor/bin/bdi.phar <command>`

_To prevent dependency conflicts, `dbrekelmans/bdi` is a PHAR-only distribution. You can install the package including dependencies with `composer require --dev dbrekelmans/browser-driver-installer`_

### Download PHAR
You can download the PHAR directly from the github [releases page](https://github.com/dbrekelmans/browser-driver-installer/releases).

Run with `bdi.phar <command>`.

## Usage
Run `bdi` or `bdi detect` to automatically detect your installed browsers and install the corresponding drivers.

If you want to install any working driver for a specific browser, run `bdi browser:<browser-name>`.
If you want to install a specific driver, run `bdi driver:<driver-name>` (defaults to the latest version. Use `--driver-version=<version>` to install a different version).
You can specify a directory where the driver will be installed with `bdi detect <path-to-direcotry>`.

For a full list of available commands, run `bdi list`.

### Supported drivers
* chromedriver
* geckodriver

### Supported browsers
* google-chrome
* chromium
* firefox
