# Statamic ImageBoss

[ImageBoss](https://imageboss.me/) integration for [Statamic CMS](https://statamic.com/).

## Requirements

- PHP 8.2+
- Laravel 11+
- Statamic 5

## Installation

```bash
composer require jorisnoo/statamic-imageboss
```

Publish the configuration:

```bash
php artisan vendor:publish --tag="statamic-imageboss-config"
```

## Configuration

Set your ImageBoss credentials in `.env`:

```env
IMAGEBOSS_SOURCE=your-source
IMAGEBOSS_SECRET=your-secret  # optional, for URL signing
```

When `IMAGEBOSS_SOURCE` is not set, the package falls back to Statamic's Glide.

### Config Options

| Option | Default | Description |
|--------|---------|-------------|
| `source` | `null` | ImageBoss source identifier |
| `secret` | `null` | HMAC secret for URL signing |
| `base_url` | `https://img.imageboss.me` | ImageBoss CDN base URL |
| `default_width` | `1000` | Default width for `url()` |
| `width_interval` | `200` | Step size for srcset generation |
| `presets` | see config | Named preset configurations |

### Presets

The package supports two approaches for defining presets: config-based and interface-based.

#### Option 1: Config-Based Presets

Define presets in `config/statamic-imageboss.php`:

```php
'presets' => [
    'thumbnail' => [
        'min' => 200,      // minimum srcset width
        'max' => 700,      // maximum srcset width
        'ratio' => 1,      // aspect ratio (optional)
        'interval' => 250, // width step (optional)
    ],
    'hero' => [
        'min' => 640,
        'max' => 3840,
    ],
],
```

#### Option 2: Interface-Based Presets

Implement the `ImagePreset` interface on your enum for self-contained presets:

```php
use Noo\StatamicImageboss\Concerns\HasImagePresetHelpers;
use Noo\StatamicImageboss\Contracts\ImagePreset;

enum Preset: string implements ImagePreset
{
    use HasImagePresetHelpers;

    case Default = 'default';
    case Thumbnail = 'thumbnail';
    case Card = 'card';
    case Hero = 'hero';

    /**
     * @return array{min: int, max: int, ratio?: float, interval?: int}
     */
    public function config(): array
    {
        return match ($this) {
            self::Default => ['min' => 320, 'max' => 2560],
            self::Thumbnail => ['min' => 200, 'max' => 700, 'ratio' => 1, 'interval' => 250],
            self::Card => ['min' => 300, 'max' => 800, 'ratio' => 4 / 5],
            self::Hero => ['min' => 640, 'max' => 3840],
        };
    }
}
```

The `HasImagePresetHelpers` trait provides convenience methods:

```php
Preset::Hero->min();      // 640
Preset::Hero->max();      // 3840
Preset::Card->ratio();    // 0.8
Preset::Thumbnail->interval(); // 250
```

## Usage

### PHP

```php
use Noo\StatamicImageboss\Facades\ImageBoss;

// Single URL
$url = ImageBoss::from($asset)->width(800)->url();
$url = ImageBoss::from($asset)->width(800)->ratio(16/9)->url();

// Responsive srcset with preset
$srcset = ImageBoss::from($asset)->preset('hero')->srcsetString();
// Or
$srcset = ImageBoss::from($asset)->preset(Preset::Hero)->srcsetString();

// Custom configuration
$srcset = ImageBoss::from($asset)
    ->min(300)
    ->max(1200)
    ->interval(200)
    ->ratio(4/5)
    ->srcsetString();
```

### Antlers

```antlers
// Single URL
{{ imageboss:url src="image" width="800" }}
{{ imageboss:url src="image" width="800" ratio="1.777" }}

// Responsive srcset
{{ imageboss:srcset src="image" preset="hero" }}
{{ imageboss:srcset src="image" min="300" max="1200" interval="150" }}
```

Full example:

```antlers
<img
    src="{{ imageboss:url src="hero" width="800" }}"
    srcset="{{ imageboss:srcset src="hero" preset="default" }}"
    sizes="100vw"
    alt="{{ hero:alt }}"
>
```

### Builder Methods

| Method | Description |
|--------|-------------|
| `width(int)` | Set image width |
| `height(int)` | Set image height |
| `ratio(float)` | Set aspect ratio (width/height) |
| `min(int)` | Minimum width for srcset |
| `max(int)` | Maximum width for srcset |
| `interval(int)` | Width step for srcset |
| `preset(string)` | Apply preset configuration |
| `url()` | Generate single URL |
| `rias()` | Generate URL with `{width}` placeholder for lazysizes RIAS |
| `srcset()` | Generate srcset array |
| `srcsetString()` | Generate srcset string |

### Example Output

`url()` returns a single URL:

```
https://img.imageboss.me/your-source/width/800/format:auto/assets/image.jpg
```

`srcsetString()` returns a comma-separated srcset string:

```
https://img.imageboss.me/.../width/640/... 640w, https://img.imageboss.me/.../width/1280/... 1280w, https://img.imageboss.me/.../width/1920/... 1920w
```

`rias()` returns a URL with `{width}` and `{height}` placeholders for [lazysizes RIAS](https://github.com/aFarkas/lazysizes/tree/gh-pages/plugins/rias):

```php
// Width only - no aspect ratio constraint
ImageBoss::from($asset)->rias();
// => https://img.imageboss.me/your-source/width/{width}/format:auto/assets/image.jpg

// With height - maintains aspect ratio via {height} placeholder
ImageBoss::from($asset)->height(630)->rias();
// => https://img.imageboss.me/your-source/cover/{width}x{height}/format:auto/assets/image.jpg

// With ratio - same result, height calculated by lazysizes
ImageBoss::from($asset)->ratio(16/9)->rias();
// => https://img.imageboss.me/your-source/cover/{width}x{height}/format:auto/assets/image.jpg
```

Lazysizes replaces `{width}` with the calculated width and `{height}` based on the `--ls-aspectratio` CSS variable.

When an asset has a focal point set, it's automatically included in the URL:

```
https://img.imageboss.me/your-source/cover:800x450/fp:0.25,0.75/format:auto/assets/image.jpg
```

## Features

- Responsive srcset generation
- Focal point support (reads from asset data)
- URL signing with HMAC-SHA256
- Automatic Glide fallback

## License

MIT
