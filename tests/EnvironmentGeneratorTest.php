<?php

namespace BeyondCode\LaravelFavicon\Tests;

use BeyondCode\LaravelFavicon\FaviconServiceProvider;

class EnvironmentGeneratorTest extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [FaviconServiceProvider::class];
    }

    /** @test */
    public function the_helper_returns_the_icon_file_for_invalid_environments()
    {
        $this->app['config']['app.env'] = 'production';

        $icon = favicon('some_icon');

        $this->assertSame('some_icon', $icon);
    }

    /** @test */
    public function the_helper_returns_the_icon_route_for_valid_environments()
    {
        $this->app['config']['app.env'] = 'local';

        $icon = favicon('some_icon');

        $this->assertSame('/laravel-favicon/some_icon', $icon);
    }
}
