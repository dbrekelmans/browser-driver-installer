# browser-driver-installer
This command-line tool helps you install browser drivers.
A common use-case is to install a browser driver to run your functional test suite.

## Why
TODO

## Installation
### Phive
`phive install dbrekelmans/browser-driver-installer`

### Composer
`composer require --dev dbrekelmans/browser-driver-installer`

### Download PHAR
You can download the PHAR directly from the github [releases page](https://github.com/dbrekelmans/browser-driver-installer/releases).

## Usage
If you want to install a specific browser driver, you can use `vendor/bin/bdi driver:<driver-name>`.
If you're not sure which driver you need, you can specify your browser, and the correct driver will automatically be installed `vendor/bin/bdi browser:<browser-name>`.

For a full list of available commands, run `vendor/bin/bdi list`.

### Supported drivers
* chromedriver (experimental)

### Supported browsers
* Google chrome (chromedriver) `vendor/bin/bdi browser:google-chrome`
