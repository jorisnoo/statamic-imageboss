<?php

namespace Noo\StatamicImageboss;

use Illuminate\Support\Str;
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

    public function width(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function height(int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function ratio(float $ratio): self
    {
        $this->ratio = $ratio;

        return $this;
    }

    public function min(int $min): self
    {
        $this->min = $min;

        return $this;
    }

    public function max(int $max): self
    {
        $this->max = $max;

        return $this;
    }

    public function interval(int $interval): self
    {
        $this->interval = $interval;

        return $this;
    }

    public function preset(string $name): self
    {
        $presets = config('statamic-imageboss.presets', []);
        $preset = $presets[$name] ?? null;

        if (! $preset) {
            return $this;
        }

        if (isset($preset['min'])) {
            $this->min = $preset['min'];
        }

        if (isset($preset['max'])) {
            $this->max = $preset['max'];
        }

        if (isset($preset['ratio'])) {
            $this->ratio = $preset['ratio'];
        }

        if (isset($preset['interval'])) {
            $this->interval = $preset['interval'];
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
            ltrim($this->asset->path(), '/'),
        );

        return $operations->join('/');
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

        [$focusX, $focusY] = Str::of($focus)->explode('-');

        return [
            'x' => round((float) $focusX / 100, 1),
            'y' => round((float) $focusY / 100, 1),
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
