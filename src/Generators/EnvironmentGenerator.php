<?php

namespace BeyondCode\LaravelFavicon\Generators;

use BeyondCode\LaravelFavicon\Favicon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
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
        if ($this->isSvgFile($icon)) {
            return $this->generateSvg($icon);
        }

        return $this->generateRaster($icon);
    }

    protected function generateRaster(string $icon): Response
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
        if (! File::exists($fullPath)) {
            $fullPath = $icon;
            if (! File::exists($fullPath)) {
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

        if (! $svgElement) {
            throw new \Exception('Invalid SVG content');
        }

        $width = $this->getSvgDimension($svgElement, 'width', 32);
        $height = $this->getSvgDimension($svgElement, 'height', 32);

        $fontSize = min($width, $height) * 0.4;
        $padding = 4;
        $paddingY = config('favicon.padding.y', 2);

        $centerX = $width / 2;
        $textY = $height - $paddingY;

        // Estimate text dimensions for background rect
        $textWidth = strlen($text) * $fontSize * 0.65;
        $textHeight = $fontSize;

        if ($backgroundColor) {
            $rectElement = $dom->createElement('rect');
            $paddingTop = $padding * 3;
            $paddingBottom = $padding;
            $rectElement->setAttribute('x', $centerX - $textWidth / 2 - $padding);
            $rectElement->setAttribute('y', $textY - $textHeight - $paddingTop);
            $rectElement->setAttribute('width', $textWidth + $padding * 2);
            $rectElement->setAttribute('height', $textHeight + $paddingTop + $paddingBottom);
            $rectElement->setAttribute('fill', $backgroundColor);
            $rectElement->setAttribute('rx', $padding);

            $svgElement->appendChild($rectElement);
        }

        $textElement = $dom->createElement('text', htmlspecialchars($text));
        $textElement->setAttribute('x', $centerX);
        $textElement->setAttribute('y', $textY);
        $textElement->setAttribute('font-family', 'Arial, sans-serif');
        $textElement->setAttribute('font-size', $fontSize);
        $textElement->setAttribute('font-weight', 'bold');
        $textElement->setAttribute('fill', $color ?: '#000000');
        $textElement->setAttribute('text-anchor', 'middle');
        $textElement->setAttribute('dominant-baseline', 'text-after-edge');

        $svgElement->appendChild($textElement);

        return $dom->saveXML();
    }

    protected function getSvgDimension(\DOMElement $svgElement, string $attribute, int $default): int
    {
        $value = $svgElement->getAttribute($attribute);

        if (! empty($value)) {
            $numericValue = preg_replace('/[^0-9.]/', '', $value);
            if ($numericValue) {
                return (int) $numericValue;
            }
        }

        // Fall back to viewBox dimensions
        $viewBox = $svgElement->getAttribute('viewBox');
        if (! empty($viewBox)) {
            $parts = preg_split('/[\s,]+/', trim($viewBox));
            if (count($parts) === 4) {
                $index = $attribute === 'width' ? 2 : 3;

                return (int) $parts[$index];
            }
        }

        return $default;
    }

    public function shouldGenerateFavicon(): bool
    {
        return config('favicon.enabled_environments.'.$this->environment) !== null;
    }
}
