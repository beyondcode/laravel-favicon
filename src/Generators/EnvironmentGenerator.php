<?php

namespace BeyondCode\LaravelFavicon\Generators;

use BeyondCode\LaravelFavicon\Favicon;
use Illuminate\Http\Response;
use Intervention\Image\ImageManager;
use Intervention\Image\Typography\FontFactory;

class EnvironmentGenerator implements FaviconGenerator
{
    protected Favicon $favicon;

    protected ImageManager $manager;

    protected string $environment;

    public function __construct(Favicon $favicon)
    {
        $this->favicon = $favicon;

        $this->manager = config('favicon.image_driver', 'gd') === 'imagick'
            ? ImageManager::imagick()
            : ImageManager::gd();

        $this->environment = config('app.env');
    }

    public function generate(string $icon): Response
    {
        $paddingX = config('favicon.padding.x');
        $paddingY = config('favicon.padding.y');

        $img = $this->manager->read($icon);

        $text = $this->favicon->getFaviconText($this->environment);
        $color = $this->favicon->getFaviconColor($this->environment);
        $bgColor = $this->favicon->getFaviconBackgroundColor($this->environment);
        $fontPath = config('favicon.font') ?? __DIR__.'/../../resources/fonts/OpenSans-Regular.ttf';

        $imgWidth = $img->width();
        $imgHeight = $img->height();

        // Calculate dynamic font size
        $fontSize = 12;
        $textSize = $this->measureText($text, $fontSize, $fontPath);

        while ($textSize['width'] + $paddingX > $imgWidth && $fontSize > 0) {
            $fontSize--;
            $textSize = $this->measureText($text, $fontSize, $fontPath);
        }

        // Create environment text overlay with background
        $textImg = $this->manager->create($textSize['width'], $textSize['height']);
        if ($bgColor) {
            $textImg->fill($bgColor);
        }
        $textImg->text($text, 0, 0, function (FontFactory $font) use ($fontPath, $fontSize, $color) {
            $font->filename($fontPath);
            $font->size($fontSize);
            $font->color($color);
            $font->valign('top');
        });

        // Compose output
        $output = $this->manager->create($imgWidth, $imgHeight);
        $output->place($img);
        $output->place($textImg, 'bottom-right', $paddingX, $paddingY);

        $encoded = $output->toPng();

        return new Response((string) $encoded, 200, [
            'Content-Type' => 'image/png',
        ]);
    }

    protected function measureText(string $text, float $fontSize, string $fontPath): array
    {
        if (function_exists('imagettfbbox')) {
            $box = imagettfbbox($fontSize, 0, $fontPath, $text);

            return [
                'width' => max(1, max($box[2], $box[4]) - min($box[0], $box[6])),
                'height' => max(1, max($box[1], $box[3]) - min($box[5], $box[7])),
            ];
        }

        if (class_exists(\Imagick::class)) {
            $draw = new \ImagickDraw();
            $draw->setFont($fontPath);
            $draw->setFontSize($fontSize);
            $imagick = new \Imagick();
            $metrics = $imagick->queryFontMetrics($draw, $text);

            return [
                'width' => max(1, (int) ceil($metrics['textWidth'])),
                'height' => max(1, (int) ceil($metrics['textHeight'])),
            ];
        }

        return [
            'width' => max(1, (int) ceil(strlen($text) * $fontSize * 0.6)),
            'height' => max(1, (int) ceil($fontSize * 1.2)),
        ];
    }

    public function shouldGenerateFavicon(): bool
    {
        return config('favicon.enabled_environments.'.$this->environment) !== null;
    }
}
