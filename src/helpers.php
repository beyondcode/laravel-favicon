<?php

use BeyondCode\LaravelFavicon\Generators\FaviconGenerator;

if (! function_exists('favicon')) {
    function favicon($image)
    {
        if (app(FaviconGenerator::class)->shouldGenerateFavicon(app()->environment())) {
            return rtrim(config('app.url'), '/').'/'.config('favicon.url_prefix')."/$image";
        }

        return $image;
    }
}
