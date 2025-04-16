<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Features\Concerns;

trait HasViewport
{
    /**
     * Set viewport dimensions.
     *
     * @param  array{width: int, height: int}|int  $width  The width in pixels or an array of dimensions
     * @param  int|null  $height  The height in pixels (required if $width is not an array)
     *
     * @throws \InvalidArgumentException If dimensions are invalid
     */
    public function viewport(array|int $width, ?int $height = null): self
    {
        if (is_array($width)) {
            $this->options['viewport'] = [
                'width' => $width['width'],
                'height' => $width['height'],
            ];

            return $this;
        }

        if ($height === null) {
            throw new \InvalidArgumentException('Height is required when width is an integer');
        }

        $this->options['viewport'] = [
            'width' => $width,
            'height' => $height,
        ];

        return $this;
    }

    /**
     * Set viewport dimensions with default properties.
     */
    public function setViewportSize(int $width, int $height): self
    {
        return $this->viewport([
            'width' => $width,
            'height' => $height,
        ]);
    }

    /**
     * Set device scale factor.
     */
    public function setDeviceScaleFactor(float $factor): self
    {
        if (! isset($this->options['viewport'])) {
            throw new \RuntimeException('Viewport must be set before setting device scale factor');
        }

        $this->options['viewport']['deviceScaleFactor'] = $factor;

        return $this;
    }

    /**
     * Enable or disable touch events.
     */
    public function setHasTouch(bool $hasTouch): self
    {
        if (! isset($this->options['viewport'])) {
            throw new \RuntimeException('Viewport must be set before setting touch events');
        }

        $this->options['viewport']['hasTouch'] = $hasTouch;

        return $this;
    }

    /**
     * Set viewport orientation.
     */
    public function setIsLandscape(bool $isLandscape): self
    {
        if (! isset($this->options['viewport'])) {
            throw new \RuntimeException('Viewport must be set before setting orientation');
        }

        $this->options['viewport']['isLandscape'] = $isLandscape;

        return $this;
    }

    /**
     * Enable or disable mobile emulation.
     */
    public function setIsMobile(bool $isMobile): self
    {
        if (! isset($this->options['viewport'])) {
            throw new \RuntimeException('Viewport must be set before setting mobile emulation');
        }

        $this->options['viewport']['isMobile'] = $isMobile;

        return $this;
    }
}
