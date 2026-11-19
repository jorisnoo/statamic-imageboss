<?php

namespace Noo\StatamicImageboss\Tags;

use Illuminate\Support\Collection;
use Noo\StatamicImageboss\ImageBoss;
use Statamic\Assets\Asset;
use Statamic\Contracts\Assets\QueryBuilder;
use Statamic\Fields\Value;
use Statamic\Tags\Tags;

class ImagebossTag extends Tags
{
    protected static $handle = 'imageboss';

    /**
     * {{ imageboss:url src="image" width="800" animation="true" }}
     */
    public function url(): string
    {
        $asset = $this->getAsset();

        if (! $asset) {
            return '';
        }

        return app(ImageBoss::class)->from($asset)
            ->width($this->params->int('width'))
            ->height($this->params->int('height'))
            ->ratio($this->params->float('ratio'))
            ->animation($this->params->bool('animation'))
            ->url();
    }

    /**
     * {{ imageboss:srcset src="image" preset="hero" }}
     */
    public function srcset(): string
    {
        $asset = $this->getAsset();

        if (! $asset) {
            return '';
        }

        $builder = app(ImageBoss::class)->from($asset);

        if ($preset = $this->params->get('preset')) {
            $builder->preset($preset);
        }

        return $builder
            ->min($this->params->int('min'))
            ->max($this->params->int('max'))
            ->interval($this->params->int('interval'))
            ->ratio($this->params->float('ratio'))
            ->animation($this->params->bool('animation'))
            ->srcsetString();
    }

    /**
     * {{ imageboss:placeholder src="image" width="800" ratio="1.777" }}
     */
    public function placeholder(): string
    {
        $asset = $this->getAsset();

        if (! $asset) {
            return '';
        }

        return app(ImageBoss::class)->from($asset)
            ->width($this->params->int('width'))
            ->height($this->params->int('height'))
            ->ratio($this->params->float('ratio'))
            ->placeholder();
    }

    private function getAsset(): ?Asset
    {
        $src = $this->params->get('src');

        if (is_string($src) && $src !== '') {
            $src = $this->context->get($src);
        }

        if ($src instanceof Value) {
            $src = $src->value();
        }

        if ($src instanceof Collection || $src instanceof QueryBuilder) {
            $src = $src->first();
        }

        return $src instanceof Asset ? $src : null;
    }
}
