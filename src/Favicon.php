<?php

namespace BeyondCode\LaravelFavicon;

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
        return array_get($this->config,'enabled_environments.'.$environment.'.text');
    }

    public function getFaviconColor(string $environment)
    {
        return array_get($this->config,'enabled_environments.'.$environment.'.color');
    }

    public function getFaviconBackgroundColor(string $environment)
    {
        return array_get($this->config,'enabled_environments.'.$environment.'.background_color');
    }
}