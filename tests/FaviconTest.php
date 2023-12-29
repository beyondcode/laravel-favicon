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
    $this->assertNull($this->favicon->getFaviconColor('unknown'));
    $this->assertSame('#000000', $this->favicon->getFaviconColor('local'));
});

it('returns environment specific text', function () {
    $this->assertNull($this->favicon->getFaviconText('unknown'));
    $this->assertSame('DEV', $this->favicon->getFaviconText('local'));
});

it('returns environment specific background color', function () {
    $this->assertNull($this->favicon->getFaviconBackgroundColor('unknown'));
    $this->assertSame('#ffffff', $this->favicon->getFaviconBackgroundColor('local'));
});

// Helpers
function getPackageProviders($app)
{
    return [FaviconServiceProvider::class];
}
