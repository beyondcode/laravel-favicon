<?php

namespace BeyondCode\LaravelFavicon\Generators;

use Illuminate\Http\Response;

interface FaviconGenerator
{
    public function generate(string $icon): Response;

    public function shouldGenerateFavicon(): bool;
}
