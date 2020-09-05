# browser-driver-installer
This command-line tool helps you install browser drivers.
A common use-case is to install a browser driver to run your functional test suite.

## Why
TODO

## Installation
### Phive (recommended)
TODO

### Download PHAR
TODO

### Composer
`composer require --dev dbrekelmans/browser-driver-installer`

## Usage
If you want to install a specific browser driver, you can use `php install driver:<driver-name>`.
If you're not sure which driver you need, you can specify your browser, and the correct driver will automatically be installed `php install browser:<browser-name>`.

For a full list of available commands, run `php install list`.

### Supported drivers
* chromedriver (experimental)

### Supported browsers
* Google chrome (chromedriver) `php install browser:google-chrome`
