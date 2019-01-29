<?php

namespace BeyondCode\LaravelFavicon\Http\Controllers;

use Illuminate\Http\Request;
use BeyondCode\LaravelFavicon\Generators\FaviconGenerator;

class FaviconController
{
    public function __invoke(Request $request, FaviconGenerator $generator)
    {
        return $generator->generate($request->route('icon'));
    }
}