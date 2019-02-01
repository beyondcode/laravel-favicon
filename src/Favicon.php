<?php

namespace BeyondCode\LaravelFavicon;

use Illuminate\Support\Arr;

class Favicon
{
    /** @var array */
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getFaviconText(string $environment)
    {
        return Arr::get($this->config, 'enabled_environments.'.$environment.'.text');
    }

    public function getFaviconColor(string $environment)
    {
        return Arr::get($this->config, 'enabled_environments.'.$environment.'.color');
    }

    public function getFaviconBackgroundColor(string $environment)
    {
        return Arr::get($this->config, 'enabled_environments.'.$environment.'.background_color');
    }
}
