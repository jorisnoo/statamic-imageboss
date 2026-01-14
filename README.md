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

| Preset | Min | Max | Ratio | Interval |
|--------|-----|-----|-------|----------|
| default | 320 | 2560 | - | 200 |
| thumbnail | 200 | 700 | 1:1 | 250 |
| card | 300 | 800 | 4:5 | 200 |
| hero | 640 | 3840 | - | 200 |

## Usage

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

### PHP

```php
use Noo\StatamicImageboss\Facades\ImageBoss;
use Noo\StatamicImageboss\Preset;

// Single URL
$url = ImageBoss::from($asset)->width(800)->url();
$url = ImageBoss::from($asset)->width(800)->ratio(16/9)->url();

// Responsive srcset
$srcset = ImageBoss::from($asset)->preset('hero')->srcsetString();
$srcset = ImageBoss::from($asset)->preset(Preset::Card)->srcsetString();

// Custom configuration
$srcset = ImageBoss::from($asset)
    ->min(300)
    ->max(1200)
    ->interval(200)
    ->ratio(4/5)
    ->srcsetString();
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
| `preset(string\|Preset)` | Apply preset configuration |
| `url()` | Generate single URL |
| `srcset()` | Generate srcset array |
| `srcsetString()` | Generate srcset string |

## Features

- Responsive srcset generation
- Focal point support (reads from asset data)
- URL signing with HMAC-SHA256
- Automatic Glide fallback

## License

MIT
