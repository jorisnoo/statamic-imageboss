<?php

namespace Noo\StatamicImageboss;

use Noo\StatamicImageboss\Contracts\ImagePreset;

class NullImageBossBuilder extends ImageBossBuilder
{
    public function __construct() {}

    public function width(?int $width): self
    {
        return $this;
    }

    public function height(?int $height): self
    {
        return $this;
    }

    public function ratio(?float $ratio): self
    {
        return $this;
    }

    public function min(?int $min): self
    {
        return $this;
    }

    public function max(?int $max): self
    {
        return $this;
    }

    public function interval(?int $interval): self
    {
        return $this;
    }

    public function preset(ImagePreset|\BackedEnum|string $preset): self
    {
        return $this;
    }

    public function url(): string
    {
        return '';
    }

    /**
     * @return array<int, array{url: string, width: int}>
     */
    public function srcset(): array
    {
        return [];
    }

    public function srcsetString(): string
    {
        return '';
    }

    public function rias(): string
    {
        return '';
    }

    public function aspectRatio(): ?float
    {
        return null;
    }
}
