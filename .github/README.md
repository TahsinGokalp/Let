<p align="center">
    <img width="130" src="logo.png">
</p>

# Lett
Laravel package for logging errors to [Lett-Tracker](https://github.com/TahsinGokalp/lett-tracker)

[![Latest Version on Packagist](https://poser.pugx.org/tahsingokalp/lett/v/stable.svg)](https://packagist.org/packages/tahsingokalp/lett)
[![Build Status](https://github.com/tahsingokalp/lett/workflows/run-tests/badge.svg)](https://github.com/tahsingokalp/lett/actions)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=TahsinGokalp_lett&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=TahsinGokalp_lett)
[![Bugs](https://sonarcloud.io/api/project_badges/measure?project=TahsinGokalp_lett&metric=bugs)](https://sonarcloud.io/summary/new_code?id=TahsinGokalp_lett)
[![Code Smells](https://sonarcloud.io/api/project_badges/measure?project=TahsinGokalp_lett&metric=code_smells)](https://sonarcloud.io/summary/new_code?id=TahsinGokalp_lett)
[![Maintainability Rating](https://sonarcloud.io/api/project_badges/measure?project=TahsinGokalp_lett&metric=sqale_rating)](https://sonarcloud.io/summary/new_code?id=TahsinGokalp_lett)
[![Reliability Rating](https://sonarcloud.io/api/project_badges/measure?project=TahsinGokalp_lett&metric=reliability_rating)](https://sonarcloud.io/summary/new_code?id=TahsinGokalp_lett)
[![Security Rating](https://sonarcloud.io/api/project_badges/measure?project=TahsinGokalp_lett&metric=security_rating)](https://sonarcloud.io/summary/new_code?id=TahsinGokalp_lett)

## Installation on laravel
You can install the package through Composer.
```bash
composer require tahsingokalp/lett
```

Then publish the config and migration file of the package using the vendor publish command.
```bash
php artisan vendor:publish --provider="TahsinGokalp\Lett\LettServiceProvider"
```
And adjust config file (`config/lett.php`) with your desired settings.

Note: by default only production environments will report errors. To modify this edit your Let configuration.

## Installation on lumen
You can install the package through Composer.
```bash
composer require tahsingokalp/lett
```

Copy the config file (`lett.php`) to lumen config directory.
```bash
php -r "file_exists('config/') || mkdir('config/'); copy('vendor/tahsingokalp/lett/config/lett.php', 'config/lett.php');"
```
And adjust config file (`config/lett.php`) with your desired settings.

In `bootstrap/app.php` you will need to:
- Uncomment this line:
    ```php
    $app->withFacades();
    ```
- Register the lett config file:
    ```php
    $app->configure('lett');
    ```
- Register lett service provider:
    ```php
    $app->register(Lett\LettServiceProvider::class);
    ```

## Configuration variables
All that is left to do is to define two env configuration variables.
```
LETT_KEY=
LETT_PROJECT_KEY=
```
`LETT_KEY` is your profile key which authorises your account to the API.

`LETT_PROJECT_KEY` is your project API key which you've received when creating a project.

Install lett-tracker to your host and get the variables

## Reporting unhandled exceptions
You can use lett as a log-channel by adding the following config to the `channels` section in `config/logging.php`:
```php
'channels' => [
    // ...
    'lett' => [
        'driver' => 'lett',
    ],
],
```
After that you can add it to the stack section:
```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'lett'],
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
