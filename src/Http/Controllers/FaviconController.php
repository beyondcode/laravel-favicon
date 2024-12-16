<?php

namespace BeyondCode\LaravelFavicon\Http\Controllers;

use BeyondCode\LaravelFavicon\Generators\FaviconGenerator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FaviconController
{
    public function __invoke(Request $request, FaviconGenerator $generator): Response
    {
        return $generator->generate($request->route('icon'));
    }
}
