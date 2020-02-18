<?php

namespace BeyondCode\LaravelFavicon\Generators;

use BeyondCode\LaravelFavicon\Favicon;
use Illuminate\Http\Response;
use Intervention\Image\AbstractFont;
use Intervention\Image\Gd\Font as GdFont;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Imagick\Font as ImagickFont;

class EnvironmentGenerator implements FaviconGenerator
{
    /** @var Favicon */
    protected $favicon;

    /** @var ImageManager */
    protected $manager;

    /** @var string */
    protected $environment;

    public function __construct(Favicon $favicon)
    {
        $this->favicon = $favicon;

        $this->manager = new ImageManager([
            'driver' => config('favicon.image_driver'),
        ]);

        $this->environment = config('app.env');
    }

    public function generate(string $icon): Response
    {
        $paddingX = config('favicon.padding.x');
        $paddingY = config('favicon.padding.y');

        $img = $this->manager->make($icon);

        $font = $this->getFont($this->favicon->getFaviconText($this->environment));

        $font->file(config('favicon.font') ?? __DIR__.'/../../resources/fonts/OpenSans-Regular.ttf');

        $font->valign('top');

        $font->color($this->favicon->getFaviconColor($this->environment));

        $this->calculateDynamicFontSize($font, $img, $paddingX);

        $environmentTextImage = $this->createEnvironmentTextImage($font);

        $output = $this->manager->canvas($img->width(), $img->height());

        $output->insert($img);

        $output->insert($environmentTextImage, 'bottom-right', $paddingX, $paddingY);

        return $output->response('png');
    }

    protected function getFont(string $text): AbstractFont
    {
        if (config('favicon.image_driver') === 'imagick') {
            return new ImagickFont($text);
        }

        return new GdFont($text);
    }

    protected function calculateDynamicFontSize(AbstractFont $font, Image $icon, $paddingX)
    {
        $size = $font->getBoxSize();

        while ($size['width'] + $paddingX > $icon->width() && $font->getSize() > 0) {
            $fontSize = $font->getSize();

            $font->size($fontSize - 1);

            $size = $font->getBoxSize();
        }
    }

    protected function createEnvironmentTextImage(AbstractFont $font)
    {
        $size = $font->getBoxSize();

        $environmentText = $this->manager->canvas($size['width'], $size['height'], $this->favicon->getFaviconBackgroundColor($this->environment));

        $font->applyToImage($environmentText);

        return $environmentText;
    }

    public function shouldGenerateFavicon(): bool
    {
        return config('favicon.enabled_environments.'.$this->environment) !== null;
    }
}
