<?php

use BeyondCode\LaravelFavicon\Favicon;
use BeyondCode\LaravelFavicon\FaviconServiceProvider;

uses(Orchestra\Testbench\TestCase::class);
beforeEach(function () {
    $this->favicon = new Favicon([
        'enabled_environments' => [
            'local' => [
                'text' => 'DEV',
                'color' => '#000000',
                'background_color' => '#ffffff',
            ],
        ],
    ]);
});


it('returns environment specific color', function () {
    expect($this->favicon->getFaviconColor('unknown'))->toBeNull()
        ->and($this->favicon->getFaviconColor('local'))->toBe('#000000');
});

it('returns environment specific text', function () {
    expect($this->favicon->getFaviconText('unknown'))->toBeNull()
        ->and($this->favicon->getFaviconText('local'))->toBe('DEV');
});

it('returns environment specific background color', function () {
    expect($this->favicon->getFaviconBackgroundColor('unknown'))->toBeNull()
        ->and($this->favicon->getFaviconBackgroundColor('local'))->toBe('#ffffff');
});

// Helpers
function getPackageProviders($app)
{
    return [FaviconServiceProvider::class];
}
