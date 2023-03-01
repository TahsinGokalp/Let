# Let
Laravel 6.x/7.x/8.x/9.x/10.x package for logging errors to [Let-Tracker](https://github.com/TahsinGokalp/let-tracker)

[![Software License](https://poser.pugx.org/tahsingokalp/let/license.svg)](LICENSE.md)
[![Latest Version on Packagist](https://poser.pugx.org/tahsingokalp/let/v/stable.svg)](https://packagist.org/packages/tahsingokalp/let)
[![Build Status](https://github.com/tahsingokalp/let/workflows/tests/badge.svg)](https://github.com/tahsingokalp/let/actions)
[![Total Downloads](https://poser.pugx.org/tahsingokalp/let/d/total.svg)](https://packagist.org/packages/tahsingokalp/let)

## Installation on laravel
You can install the package through Composer.
```bash
composer require tahsingokalp/let
```

Then publish the config and migration file of the package using the vendor publish command.
```bash
php artisan vendor:publish --provider="Let\ServiceProvider"
```
And adjust config file (`config/let.php`) with your desired settings.

Note: by default only production environments will report errors. To modify this edit your Let configuration.

## Installation on lumen
You can install the package through Composer.
```bash
composer require tahsingokalp/let
```

Copy the config file (`let.php`) to lumen config directory.
```bash
php -r "file_exists('config/') || mkdir('config/'); copy('vendor/tahsingokalp/let/config/larletabug.php', 'config/let.php');"
```
And adjust config file (`config/let.php`) with your desired settings.

In `bootstrap/app.php` you will need to:
- Uncomment this line:
    ```php
    $app->withFacades();
    ```
- Register the let config file:
    ```php
    $app->configure('let');
    ```
- Register let service provider:
    ```php
    $app->register(Let\ServiceProvider::class);
    ```

## Configuration variables
All that is left to do is to define two env configuration variables.
```
L_KEY=
L_PROJECT_KEY=
```
`L_KEY` is your profile key which authorises your account to the API.

`L_PROJECT_KEY` is your project API key which you've received when creating a project.

Install let-tracker to your host and get the variables

## Reporting unhandled exceptions
You can use Let as a log-channel by adding the following config to the `channels` section in `config/logging.php`:
```php
'channels' => [
    // ...
    'let' => [
        'driver' => 'let',
    ],
],
```
After that you can add it to the stack section:
```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'let'],
    ],
    //...
],
```

PS: If you're using lumen, it could be that you don't have the `logging.php` file. So, you can use default logging file from
framework core and make changes above.
```bash
php -r "file_exists('config/') || mkdir('config/'); copy('vendor/laravel/lumen-framework/config/logging.php', 'config/logging.php');"
```

## License
The Let package is open source software licensed under the [license MIT](http://opensource.org/licenses/MIT)

## Special Thanks

This repo forked from https://github.com/LaraBug/LaraBug - https://github.com/Cannonb4ll
