# Laravel Favicon 

[![Latest Version on Packagist](https://img.shields.io/packagist/v/beyondcode/laravel-favicon.svg?style=flat-square)](https://packagist.org/packages/beyondcode/laravel-favicon)
[![Build Status](https://img.shields.io/travis/beyondcode/laravel-favicon/master.svg?style=flat-square)](https://travis-ci.org/beyondcode/laravel-favicon)
[![Quality Score](https://img.shields.io/scrutinizer/g/beyondcode/laravel-favicon.svg?style=flat-square)](https://scrutinizer-ci.com/g/beyondcode/laravel-favicon)
[![Total Downloads](https://img.shields.io/packagist/dt/beyondcode/laravel-favicon.svg?style=flat-square)](https://packagist.org/packages/beyondcode/laravel-favicon)

Create dynamic favicons based on your environment settings.

![](https://beyondco.de/github/favicons/screenshot.png)

## Laravel Package Development

[![https://phppackagedevelopment.com](https://beyondco.de/courses/phppd.jpg)](https://phppackagedevelopment.com)

If you want to learn how to create reusable PHP packages yourself, take a look at my upcoming [PHP Package Development](https://phppackagedevelopment.com) video course.

## Installation

You can install the package via composer:

```bash
composer require beyondcode/laravel-favicon
```

The service provider for this package will be automatically registered for you.

## Usage

To make use of this package, make use of the `favicon` helper function that this package provides.

You can simply wrap the function around your favicon icon names, like this:

```html
<link rel="icon" type="image/png" sizes="32x32" href="{{ favicon(asset('favicon-32x32.png')) }}">

<link rel="shortcut icon" href="{{ favicon('favicon.ico') }}" />
```

## Customization

You can completely customize which environments you want to have enabled for the favicon generation, as well as the font and colors that will be used.

To modify the default values, publish the package configuration file using:

```
php artisan vendor:publish --provider='BeyondCode\LaravelFavicon\FaviconServiceProvider' --tag='config'
```

This will publish the `config/favicon.php` file.

This is what the default content looks like:

```php
return [

    /*
     * The list of enabled environments for the dynamic favicon
     * generation. You can specify the text to display as well
     * as the font and background color for the text.
     *
     * If no background color is specified, the text will be
     * on a transparent background.
     */
    'enabled_environments' => [
        'local' => [
            'text' => 'DEV',
            'color' => '#000000',
            'background_color' => '#ffffff',
        ],
    ],

    /*
     * The dynamic favicon text padding to apply.
     */
    'padding' => [
        'x' => 2,
        'y' => 2,
    ],

    /*
     * The font file to use for the dynamic favicon generation.
     * The default value will use OpenSans Regular.
     */
    'font' => null,

    /*
    * Intervention Image supports "GD Library" and "Imagick" to process images
    * internally. You may choose one of them according to your PHP
    * configuration. By default PHP's "GD Library" implementation is used.
    *
    * If you want to convert ICO files, you need to use imagick.
    *
    * Supported: "gd", "imagick"
    *
    */
    'image_driver' => 'gd',

    /*
     * The prefix to use for the dynamic favicon URL.
     */
    'url_prefix' => 'laravel-favicon',

    /*
     * The favicon generator class to use. The default generator
     * makes use of the environment settings defined in this file.
     * But you can create your own favicon generator if you want.
     */
    'generator' => \BeyondCode\LaravelFavicon\Generators\EnvironmentGenerator::class,


];
```

Modify the settings to suit your needs.

## Custom generator

The default favicon generator will write the text on the bottom-right corner of your favicon, in the desired color, font and background-color.
If you want to generate a completely custom favicon, you can create your own FaviconGenerator implementation class and set it in the configuration file.

This is the interface that the generator should implement:

```php
interface FaviconGenerator
{
    public function generate(string $icon): Response;

    public function shouldGenerateFavicon(): bool;
}
```

The `generate` method receives the icon url/filename and expects you to return an illuminate HTTP response.

The `shouldGenerateFavicon` method can be used to determine if a custom favicon should get generated.

## FAQ

- My ICO files are not working, why?

In order to modify ICO files, you need the Imagick PHP library installed and enabled in your `config/favicon.php` file.

- Is there a performance impact when I'm using this package?

No - the default generator only modifies your favicon when the specified environment is enabled. This means, that production environments only see the static assets that you already have.

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email marcel@beyondco.de instead of using the issue tracker.

## Credits

- [Marcel Pociot](https://github.com/mpociot)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
