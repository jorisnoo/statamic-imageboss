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

    private bool $animation = false;

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

    public function animation(?bool $animation = true): self
    {
        if (! $animation) {
            return $this;
        }

        $this->animation = true;

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
            $availablePresets = array_keys(config('statamic.imageboss.presets', []));

            if (! in_array($presetName, $availablePresets, true)) {
                return $this;
            }

            $config = config("statamic.imageboss.presets.{$presetName}", []);
        }

        $this->min($config['min'] ?? null)
            ->max($config['max'] ?? null)
            ->ratio($config['ratio'] ?? null)
            ->interval($config['interval'] ?? null);

        if (isset($config['animation'])) {
            $this->animation = $config['animation'];
        }

        return $this;
    }

    public function url(): string
    {
        $width = $this->width ?? $this->configuredWidth('default_width', 1000);
        $height = $this->calculateHeight($width) ?? $this->height;

        $source = config('statamic.imageboss.source');

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

                $source = config('statamic.imageboss.source');

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
        $source = config('statamic.imageboss.source');

        if (! $source) {
            return $this->url();
        }

        $baseUrl = config('statamic.imageboss.base_url', 'https://img.imageboss.me');
        $height = ($this->height || $this->ratio) ? '{height}' : null;

        return $baseUrl.$this->buildImageBossPath('{width}', $height);
    }

    public function placeholder(): string
    {
        [$width, $height] = $this->resolvePlaceholderDimensions();

        if ($width === null || $height === null) {
            return '';
        }

        // Fully encoded: unencoded spaces would split the URI into bogus srcset candidates.
        return 'data:image/svg+xml;charset=utf-8,'.rawurlencode(
            "<svg xmlns='http://www.w3.org/2000/svg' width='{$width}' height='{$height}'/>"
        );
    }

    /**
     * @return array{?int, ?int}
     */
    private function resolvePlaceholderDimensions(): array
    {
        if ($this->width && $this->height) {
            return [$this->width, $this->height];
        }

        if ($this->ratio) {
            $width = $this->width ?? ($this->asset->width() ?: $this->max ?? $this->configuredWidth('default_width', 1000));

            return [$width, $this->calculateHeight($width)];
        }

        $assetWidth = $this->asset->width();
        $assetHeight = $this->asset->height();

        if ($assetWidth && $assetHeight) {
            return [$assetWidth, $assetHeight];
        }

        return [null, null];
    }

    private function buildImageBossPath(int|string $width, int|string|null $height): string
    {
        $source = config('statamic.imageboss.source');
        $operations = collect(['', $source]);

        if ($height) {
            $operations->push("cover/{$width}x{$height}");
        } else {
            $operations->push("width/{$width}");
        }

        $focalPoint = $this->getFocalPoint() ?? ['x' => 0.5, 'y' => 0.5];

        $options = "fp-x:{$focalPoint['x']},fp-y:{$focalPoint['y']},format:auto";

        if ($this->animation) {
            $options .= ',animation:true';
        }

        $operations->push($options);

        $operations->push(OriginPath::for($this->asset));

        return $operations->join('/');
    }

    private function signPath(string $path): string
    {
        $baseUrl = config('statamic.imageboss.base_url', 'https://img.imageboss.me');
        $token = config('statamic.imageboss.token');

        if (! $token) {
            return $baseUrl.$path;
        }

        $bossToken = hash_hmac('sha256', $path, $token);

        return $baseUrl.$path.'?bossToken='.$bossToken;
    }

    private function generateGlideUrl(int $width, ?int $height): string
    {
        $manipulation = Image::manipulate($this->asset)->width($width);

        if ($height) {
            $manipulation->height($height)->fit('crop_focal');
        }

        return URL::makeAbsolute($manipulation->build());
    }

    private function configuredWidth(string $key, int $default): int
    {
        $value = filter_var(config('statamic.imageboss.'.$key), FILTER_VALIDATE_INT);

        return $value > 0 ? $value : $default;
    }

    /**
     * @return array<int>
     */
    private function generateWidths(): array
    {
        $min = $this->min ?? $this->configuredWidth('presets.default.min', 320);
        $max = $this->max ?? $this->configuredWidth('presets.default.max', 2560);
        $interval = $this->interval ?? $this->configuredWidth('width_interval', 200);

        if (intdiv($max - $min, $interval) >= 1024) {
            throw new \InvalidArgumentException('Srcset exceeds 1024 width intervals; increase the interval or reduce the range.');
        }

        $widths = [];

        for ($w = $min; $w < $max; $w += $interval) {
            $widths[] = $w;
        }

        $widths[] = $max;

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

        if ($parts->count() < 2 || $parts->count() > 3) {
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
            'x' => round($focusX / 100, 4),
            'y' => round($focusY / 100, 4),
        ];
    }

    private function calculateHeight(int $width): ?int
    {
        $ratio = $this->aspectRatio();

        if ($ratio) {
            return (int) round($width / $ratio);
        }

        return null;
    }
}
