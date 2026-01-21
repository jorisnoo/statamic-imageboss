<?php

namespace Noo\StatamicImageboss\Tags;

use Noo\StatamicImageboss\ImageBoss;
use Statamic\Assets\Asset;
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

        $width = $this->params->get('width');
        $height = $this->params->get('height');
        $ratio = $this->params->get('ratio');

        $builder = app(ImageBoss::class)->from($asset)
            ->width($width ? (int) $width : null)
            ->height($height ? (int) $height : null)
            ->ratio($ratio ? (float) $ratio : null);

        return $builder->url();
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

        $preset = $this->params->get('preset');
        $min = $this->params->get('min');
        $max = $this->params->get('max');
        $interval = $this->params->get('interval');
        $ratio = $this->params->get('ratio');

        $builder = app(ImageBoss::class)->from($asset);

        if ($preset) {
            $builder->preset($preset);
        }

        $builder
            ->min($min ? (int) $min : null)
            ->max($max ? (int) $max : null)
            ->interval($interval ? (int) $interval : null)
            ->ratio($ratio ? (float) $ratio : null);

        return $builder->srcsetString();
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

        if (is_object($contextValue) && method_exists($contextValue, 'value')) {
            $resolved = $contextValue->value();

            if ($resolved instanceof Asset) {
                return $resolved;
            }
        }

        return null;
    }
}
