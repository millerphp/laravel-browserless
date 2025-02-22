<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Features\Concerns;

trait HasViewport
{
    /**
     * Set viewport dimensions and properties.
     *
     * @param array{
     *   width: int,
     *   height: int,
     *   deviceScaleFactor?: float,
     *   hasTouch?: bool,
     *   isLandscape?: bool,
     *   isMobile?: bool
     * } $options
     */
    public function viewport(array $options): self
    {
        if (! isset($options['width']) || ! isset($options['height'])) {
            throw new \InvalidArgumentException('Viewport must have width and height');
        }

        $this->options['viewport'] = array_merge([
            'deviceScaleFactor' => 1.0,
            'hasTouch' => false,
            'isLandscape' => false,
            'isMobile' => false,
        ], $options);

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
