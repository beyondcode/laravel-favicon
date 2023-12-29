<?php

use BeyondCode\LaravelFavicon\FaviconServiceProvider;

uses(Orchestra\Testbench\TestCase::class);

test('the helper returns the icon file for invalid environments', function () {
    app()['config']['app.env'] = 'production';

    $icon = favicon('some_icon');

    expect($icon)->toBe('some_icon');
});

test('the helper returns the icon route for valid environments', function () {
    app()['config']['app.env'] = 'local';

    $icon = favicon('some_icon');

    expect($icon)->toBe('/laravel-favicon/some_icon');
});

// Helpers
function getPackageProviders($app)
{
    return [FaviconServiceProvider::class];
}
