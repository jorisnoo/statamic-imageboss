<?php

namespace Noo\StatamicImageboss;

use Illuminate\Support\Str;
use Noo\StatamicImageboss\Contracts\ImagePreset;
use Statamic\Assets\Asset;
use Statamic\Facades\Image;
use Statamic\Facades\URL;

class ImageBossBuilder
{
    private Asset $asset;

    private ?int $width = null;

    private ?int $height = null;

    private ?float $ratio = null;

    private ?int $min = null;

    private ?int $max = null;

    private ?int $interval = null;

    public function __construct(Asset $asset)
    {
        $this->asset = $asset;
    }

    public function width(?int $width): self
    {
        if ($width === null || $width < 1) {
            return $this;
        }

        $this->width = $width;

        return $this;
    }

    public function height(?int $height): self
    {
        if ($height === null || $height < 1) {
            return $this;
        }

        $this->height = $height;

        return $this;
    }

    public function ratio(?float $ratio): self
    {
        if ($ratio === null || $ratio <= 0) {
            return $this;
        }

        $this->ratio = $ratio;

        return $this;
    }

    public function min(?int $min): self
    {
        if ($min === null || $min < 1) {
            return $this;
        }

        $this->min = $min;

        return $this;
    }

    public function max(?int $max): self
    {
        if ($max === null || $max < 1) {
            return $this;
        }

        $this->max = $max;

        return $this;
    }

    public function interval(?int $interval): self
    {
        if ($interval === null || $interval < 1) {
            return $this;
        }

        $this->interval = $interval;

        return $this;
    }

    public function aspectRatio(): ?float
    {
        if ($this->ratio) {
            return $this->ratio;
        }

        if ($this->width && $this->height) {
            return $this->width / $this->height;
        }

        return null;
    }

    public function preset(ImagePreset|\BackedEnum|string $preset): self
    {
        if ($preset instanceof ImagePreset) {
            $config = $preset->config();
        } else {
            $presetName = $preset instanceof \BackedEnum ? $preset->value : $preset;
            $availablePresets = array_keys(config('statamic-imageboss.presets', []));

            if (! in_array($presetName, $availablePresets, true)) {
                return $this;
            }

            $config = config("statamic-imageboss.presets.{$presetName}", []);
        }

        if (empty($config)) {
            return $this;
        }

        if (isset($config['min'])) {
            $this->min = $config['min'];
        }

        if (isset($config['max'])) {
            $this->max = $config['max'];
        }

        if (isset($config['ratio'])) {
            $this->ratio = $config['ratio'];
        }

        if (isset($config['interval'])) {
            $this->interval = $config['interval'];
        }

        return $this;
    }

    public function url(): string
    {
        $width = $this->width ?? config('statamic-imageboss.default_width', 1000);
        $height = $this->calculateHeight($width);

        $source = config('statamic-imageboss.source');

        if (! $source) {
            return $this->generateGlideUrl($width, $height);
        }

        $path = $this->buildImageBossPath($width, $height);

        return $this->signPath($path);
    }

    /**
     * @return array<int, array{url: string, width: int}>
     */
    public function srcset(): array
    {
        $widths = $this->generateWidths();

        return collect($widths)
            ->map(function (int $width) {
                $height = $this->calculateHeight($width);

                $source = config('statamic-imageboss.source');

                if (! $source) {
                    $url = $this->generateGlideUrl($width, $height);
                } else {
                    $path = $this->buildImageBossPath($width, $height);
                    $url = $this->signPath($path);
                }

                return [
                    'url' => $url,
                    'width' => $width,
                ];
            })
            ->all();
    }

    public function srcsetString(): string
    {
        return collect($this->srcset())
            ->map(fn (array $item) => "{$item['url']} {$item['width']}w")
            ->join(', ');
    }

    /**
     * Generate a RIAS (Responsive Images as a Service) URL with {width} and {height} placeholders.
     *
     * Note: RIAS URLs cannot be signed because the placeholders are replaced at runtime
     * by the client (e.g., lazysizes), which would invalidate any pre-computed signature.
     */
    public function rias(): string
    {
        $source = config('statamic-imageboss.source');

        if (! $source) {
            return $this->url();
        }

        $baseUrl = config('statamic-imageboss.base_url', 'https://img.imageboss.me');
        $height = ($this->height || $this->ratio) ? '{height}' : null;

        $operations = collect(['', $source]);

        if ($height) {
            $operations->push("cover/{width}x{$height}");
        } else {
            $operations->push('width/{width}');
        }

        $focalPoint = $this->getFocalPoint();

        if ($focalPoint) {
            $operations->push("fp-x:{$focalPoint['x']},fp-y:{$focalPoint['y']},format:auto");
        } else {
            $operations->push('format:auto');
        }

        $operations->push(
            $this->asset->container()->disk()->name ?? $this->asset->container()->handle(),
            $this->sanitizePath($this->asset->path()),
        );

        return $baseUrl.$operations->join('/');
    }

    private function buildImageBossPath(int $width, ?int $height): string
    {
        $source = config('statamic-imageboss.source');
        $operations = collect(['', $source]);

        if ($height) {
            $operations->push("cover/{$width}x{$height}");
        } else {
            $operations->push("width/{$width}");
        }

        $focalPoint = $this->getFocalPoint();

        if ($focalPoint) {
            $operations->push("fp-x:{$focalPoint['x']},fp-y:{$focalPoint['y']},format:auto");
        } else {
            $operations->push('format:auto');
        }

        $operations->push(
            $this->asset->container()->disk()->name ?? $this->asset->container()->handle(),
            $this->sanitizePath($this->asset->path()),
        );

        return $operations->join('/');
    }

    private function sanitizePath(string $path): string
    {
        $path = str_replace(['\\', '..'], ['/', ''], $path);
        $path = (string) preg_replace('#/+#', '/', $path);

        return ltrim($path, '/');
    }

    private function signPath(string $path): string
    {
        $baseUrl = config('statamic-imageboss.base_url', 'https://img.imageboss.me');
        $secret = config('statamic-imageboss.secret');

        if (! $secret) {
            return $baseUrl.$path;
        }

        $bossToken = hash_hmac('sha256', $path, $secret);

        return $baseUrl.$path.'?bossToken='.$bossToken;
    }

    private function generateGlideUrl(int $width, ?int $height): string
    {
        $manipulation = Image::manipulate($this->asset)->width($width);

        if ($height) {
            $manipulation->height($height);
        }

        return URL::makeAbsolute($manipulation->build());
    }

    /**
     * @return array<int>
     */
    private function generateWidths(): array
    {
        $min = $this->min ?? config('statamic-imageboss.presets.default.min', 320);
        $max = $this->max ?? config('statamic-imageboss.presets.default.max', 2560);
        $interval = $this->interval ?? config('statamic-imageboss.width_interval', 320);

        $widths = [];

        for ($w = $min; $w <= $max; $w += $interval) {
            $widths[] = $w;
        }

        if (end($widths) !== $max) {
            $widths[] = $max;
        }

        return $widths;
    }

    /**
     * @return array{x: float, y: float}|null
     */
    private function getFocalPoint(): ?array
    {
        if (! $this->asset->data()->has('focus')) {
            return null;
        }

        $focus = $this->asset->data()->get('focus');

        if (! is_string($focus) || ! str_contains($focus, '-')) {
            return null;
        }

        $parts = Str::of($focus)->explode('-');

        if ($parts->count() !== 2) {
            return null;
        }

        [$focusX, $focusY] = $parts;

        if (! is_numeric($focusX) || ! is_numeric($focusY)) {
            return null;
        }

        $focusX = (float) $focusX;
        $focusY = (float) $focusY;

        if ($focusX < 0 || $focusX > 100 || $focusY < 0 || $focusY > 100) {
            return null;
        }

        return [
            'x' => round($focusX / 100, 1),
            'y' => round($focusY / 100, 1),
        ];
    }

    private function calculateHeight(int $width): ?int
    {
        if ($this->height) {
            return $this->height;
        }

        if (! $this->ratio) {
            return null;
        }

        return (int) round($width / $this->ratio);
    }
}
