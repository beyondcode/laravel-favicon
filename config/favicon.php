<?php

return [

    /*
     * The list of enabled environments for the dynamic favicon
     * generation. You can specify the text to display as well
     * as the font and background color for the text.
     *
     * If no background color is specified, the text will be
     * on a transparent background.
     */
    'enabled_environments' => [
        'local' => [
            'text' => 'DEV',
            'color' => '#000000',
            'background_color' => '#ffffff',
        ],
    ],

    /*
     * The dynamic favicon text padding to apply.
     */
    'padding' => [
        'x' => 2,
        'y' => 2,
    ],

    /*
     * The font file to use for the dynamic favicon generation.
     * The default value will use OpenSans Regular.
     */
    'font' => null,

    /*
    * Intervention Image supports "GD Library" and "Imagick" to process images
    * internally. You may choose one of them according to your PHP
    * configuration. By default PHP's "GD Library" implementation is used.
    *
    * If you want to convert ICO files, you need to use imagick.
    *
    * Supported: "gd", "imagick"
    *
    */
    'image_driver' => 'gd',

    /*
     * The prefix to use for the dynamic favicon URL.
     */
    'url_prefix' => 'laravel-favicon',

    /*
     * The favicon generator class to use. The default generator
     * makes use of the environment settings defined in this file.
     * But you can create your own favicon generator if you want.
     */
    'generator' => \BeyondCode\LaravelFavicon\Generators\EnvironmentGenerator::class,

];
