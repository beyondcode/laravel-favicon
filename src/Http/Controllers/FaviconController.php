<?php

namespace BeyondCode\LaravelFavicon\Http\Controllers;

use BeyondCode\LaravelFavicon\Generators\FaviconGenerator;
use Illuminate\Http\Request;

class FaviconController
{
    public function __invoke(Request $request, FaviconGenerator $generator)
    {
        return $generator->generate($request->route('icon'));
    }
}
