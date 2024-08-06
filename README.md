# ecow

[![Latest Version on Packagist](https://img.shields.io/packagist/v/inmanturbo/ecow.svg?style=flat-square)](https://packagist.org/packages/inmanturbo/ecow)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/inmanturbo/ecow/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/inmanturbo/ecow/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/inmanturbo/ecow/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/inmanturbo/ecow/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/inmanturbo/ecow.svg?style=flat-square)](https://packagist.org/packages/inmanturbo/ecow)

## Eloquent copy-on-write: automatically copy all model changes to a seperate table.

<img src="art/ecow.svg" width="419px" alt="ecow" />

## Installation

You can install the package via composer:

```bash
composer require inmanturbo/ecow
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="ecow-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="ecow-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="ecow-views"
```

## Usage

```php
$ecow = new Inmanturbo\Ecow();
echo $ecow->echoPhrase('Hello, Inmanturbo!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [inmanturbo](https://github.com/inmanturbo)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
