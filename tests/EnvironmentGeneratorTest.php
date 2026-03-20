<?php

namespace BeyondCode\LaravelFavicon\Tests;

use BeyondCode\LaravelFavicon\FaviconServiceProvider;
use PHPUnit\Framework\Attributes\Test;

class EnvironmentGeneratorTest extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [FaviconServiceProvider::class];
    }

    #[Test]
    public function the_helper_returns_the_icon_file_for_invalid_environments()
    {
        $this->app['config']['app.env'] = 'production';

        $icon = favicon('some_icon');

        $this->assertSame('some_icon', $icon);
    }

    #[Test]
    public function the_helper_returns_the_icon_route_for_valid_environments()
    {
        $this->app['config']['app.env'] = 'local';

        $icon = favicon('some_icon');

        $expected = rtrim(config('app.url'), '/').'/'.config('favicon.url_prefix').'/some_icon';
        $this->assertSame($expected, $icon);
    }
}
