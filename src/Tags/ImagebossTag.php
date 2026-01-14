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

        $builder = app(ImageBoss::class)->from($asset);

        if ($width = $this->params->get('width')) {
            $builder->width((int) $width);
        }

        if ($height = $this->params->get('height')) {
            $builder->height((int) $height);
        }

        if ($ratio = $this->params->get('ratio')) {
            $builder->ratio((float) $ratio);
        }

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

        $builder = app(ImageBoss::class)->from($asset);

        if ($preset = $this->params->get('preset')) {
            $builder->preset($preset);
        }

        if ($min = $this->params->get('min')) {
            $builder->min((int) $min);
        }

        if ($max = $this->params->get('max')) {
            $builder->max((int) $max);
        }

        if ($interval = $this->params->get('interval')) {
            $builder->interval((int) $interval);
        }

        if ($ratio = $this->params->get('ratio')) {
            $builder->ratio((float) $ratio);
        }

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
