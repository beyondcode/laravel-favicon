<?php

namespace BeyondCode\LaravelFavicon\Tests;

use BeyondCode\LaravelFavicon\Favicon;
use BeyondCode\LaravelFavicon\FaviconServiceProvider;

class FaviconTest extends \Orchestra\Testbench\TestCase
{
    /** @var Favicon */
    protected $favicon;

    public function setUp()
    {
        parent::setUp();

        $this->favicon = new Favicon([
            'enabled_environments' => [
                'local' => [
                    'text' => 'DEV',
                    'color' => '#000000',
                    'background_color' => '#ffffff',
                ],
            ],
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [FaviconServiceProvider::class];
    }

    /** @test */
    public function it_returns_environment_specific_color()
    {
        $this->assertNull($this->favicon->getFaviconColor('unknown'));
        $this->assertSame('#000000', $this->favicon->getFaviconColor('local'));
    }

    /** @test */
    public function it_returns_environment_specific_text()
    {
        $this->assertNull($this->favicon->getFaviconText('unknown'));
        $this->assertSame('DEV', $this->favicon->getFaviconText('local'));
    }

    /** @test */
    public function it_returns_environment_specific_background_color()
    {
        $this->assertNull($this->favicon->getFaviconBackgroundColor('unknown'));
        $this->assertSame('#ffffff', $this->favicon->getFaviconBackgroundColor('local'));
    }
}
