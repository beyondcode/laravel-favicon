<?php

namespace BeyondCode\LaravelFavicon\Generators;

use BeyondCode\LaravelFavicon\Favicon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
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
        if ($this->isSvgFile($icon)) {
            return $this->generateSvg($icon);
        }

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

    protected function isSvgFile(string $icon): bool
    {
        return pathinfo($icon, PATHINFO_EXTENSION) === 'svg';
    }

    protected function generateSvg(string $icon): Response
    {
        $svgContent = $this->getSvgContent($icon);
        $modifiedSvg = $this->addTextToSvg($svgContent);

        return response($modifiedSvg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    protected function getSvgContent(string $icon): string
    {
        if (filter_var($icon, FILTER_VALIDATE_URL)) {
            $content = @file_get_contents($icon);
            if ($content === false) {
                throw new \Exception("Could not fetch SVG content from URL: {$icon}");
            }
            return $content;
        }

        $fullPath = public_path($icon);
        if (!File::exists($fullPath)) {
            $fullPath = $icon;
            if (!File::exists($fullPath)) {
                throw new \Exception("SVG file not found: {$icon}");
            }
        }

        return File::get($fullPath);
    }

    protected function addTextToSvg(string $svgContent): string
    {
        $text = $this->favicon->getFaviconText($this->environment);
        $color = $this->favicon->getFaviconColor($this->environment);
        $backgroundColor = $this->favicon->getFaviconBackgroundColor($this->environment);

        if (empty($text)) {
            return $svgContent;
        }

        $dom = new \DOMDocument();
        $dom->loadXML($svgContent);
        $svgElement = $dom->getElementsByTagName('svg')->item(0);

        if (!$svgElement) {
            throw new \Exception("Invalid SVG content");
        }

        $width = $this->getSvgDimension($svgElement, 'width', 32);
        $height = $this->getSvgDimension($svgElement, 'height', 32);

        $fontSize = min($width, $height) * 0.25;
        $paddingX = config('favicon.padding.x', 2);
        $paddingY = config('favicon.padding.y', 2);

        $textX = $width - $paddingX;
        $textY = $height - $paddingY;

        $textElement = $dom->createElement('text', htmlspecialchars($text));
        $textElement->setAttribute('x', $textX);
        $textElement->setAttribute('y', $textY);
        $textElement->setAttribute('font-family', 'Arial, sans-serif');
        $textElement->setAttribute('font-size', $fontSize);
        $textElement->setAttribute('font-weight', 'bold');
        $textElement->setAttribute('fill', $color ?: '#000000');
        $textElement->setAttribute('text-anchor', 'end');
        $textElement->setAttribute('dominant-baseline', 'text-bottom');

        if ($backgroundColor) {
            $textWidth = strlen($text) * $fontSize * 0.6;
            $textHeight = $fontSize;

            $rectElement = $dom->createElement('rect');
            $rectElement->setAttribute('x', $textX - $textWidth - 2);
            $rectElement->setAttribute('y', $textY - $textHeight);
            $rectElement->setAttribute('width', $textWidth + 4);
            $rectElement->setAttribute('height', $textHeight + 2);
            $rectElement->setAttribute('fill', $backgroundColor);
            $rectElement->setAttribute('rx', 2);

            $svgElement->appendChild($rectElement);
        }

        $svgElement->appendChild($textElement);

        return $dom->saveXML();
    }

    protected function getSvgDimension(\DOMElement $svgElement, string $attribute, int $default): int
    {
        $value = $svgElement->getAttribute($attribute);

        if (empty($value)) {
            return $default;
        }

        $numericValue = preg_replace('/[^0-9.]/', '', $value);

        return $numericValue ? (int) $numericValue : $default;
    }

    public function shouldGenerateFavicon(): bool
    {
        return config('favicon.enabled_environments.'.$this->environment) !== null;
    }
}
