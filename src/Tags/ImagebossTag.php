<?php

namespace Noo\StatamicImageboss\Tags;

use Noo\StatamicImageboss\ImageBoss;
use Statamic\Assets\Asset;
use Statamic\Fields\Value;
use Statamic\Tags\Tags;

class ImagebossTag extends Tags
{
    protected static $handle = 'imageboss';

    /**
     * {{ imageboss:url src="image" width="800" }}
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
            ->srcsetString();
    }

    private function getAsset(): ?Asset
    {
        $src = $this->params->get('src');

        if ($src instanceof Asset) {
            return $src;
        }

        $contextValue = $this->context->get($src);

        if ($contextValue instanceof Asset) {
            return $contextValue;
        }

        if ($contextValue instanceof Value) {
            $resolved = $contextValue->value();

            return $resolved instanceof Asset ? $resolved : null;
        }

        return null;
    }
}
