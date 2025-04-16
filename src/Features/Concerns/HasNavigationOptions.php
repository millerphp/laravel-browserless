<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Features\Concerns;

trait HasNavigationOptions
{
    /**
     * Set navigation options.
     *
     * @param array{
     *   referer?: string,
     *   referrerPolicy?: string,
     *   timeout?: int,
     *   waitUntil?: string|array<string>
     * } $options
     */
    protected function setNavigationOptions(array $options): void
    {
        if (! isset($this->options['gotoOptions'])) {
            $this->options['gotoOptions'] = [];
        }

        foreach ($options as $key => $value) {
            if ($key === 'waitUntil' && ! is_string($value) && ! is_array($value)) {
                throw new \InvalidArgumentException('waitUntil must be a string or array of strings');
            }

            if ($key === 'waitUntil' && is_string($value) && ! in_array($value, ['domcontentloaded', 'load', 'networkidle0', 'networkidle2'])) {
                throw new \InvalidArgumentException('Invalid waitUntil value');
            }

            $this->options['gotoOptions'][$key] = $value;
        }
    }

    /**
     * Set the referer header.
     */
    public function referer(string $referer): self
    {
        $this->setNavigationOptions(['referer' => $referer]);

        return $this;
    }

    /**
     * Set the referrer policy.
     */
    public function referrerPolicy(string $policy): self
    {
        $this->setNavigationOptions(['referrerPolicy' => $policy]);

        return $this;
    }

    /**
     * Set navigation timeout in milliseconds.
     */
    public function navigationTimeout(int $timeout): self
    {
        $this->setNavigationOptions(['timeout' => $timeout]);

        return $this;
    }

    /**
     * Set when to consider navigation succeeded.
     *
     * @param  string|array<string>  $events  One of: 'domcontentloaded', 'load', 'networkidle0', 'networkidle2'
     */
    public function waitUntil(string|array $events): self
    {
        $this->setNavigationOptions(['waitUntil' => $events]);

        return $this;
    }

    /**
     * Set navigation options.
     *
     * @param  array<string>|string  $options  The navigation options or a single option
     */
    public function navigationOptions(array|string $options): self
    {
        if (is_string($options)) {
            $this->options['navigationOptions'] = [$options];

            return $this;
        }

        $this->options['navigationOptions'] = $options;

        return $this;
    }
}
