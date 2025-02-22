<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Features\Concerns;

trait HasResourceInjection
{
    /**
     * Add a script tag to the page.
     *
     * @param array{
     *   url?: string,
     *   path?: string,
     *   content?: string,
     *   type?: string,
     *   id?: string
     * } $options
     */
    public function addScriptTag(array $options): self
    {
        if (! isset($options['url']) && ! isset($options['path']) && ! isset($options['content'])) {
            throw new \InvalidArgumentException('Script tag must have either url, path, or content');
        }

        if (! isset($this->options['addScriptTag'])) {
            $this->options['addScriptTag'] = [];
        }

        $this->options['addScriptTag'][] = $options;

        return $this;
    }

    /**
     * Add a style tag to the page.
     *
     * @param array{
     *   url?: string,
     *   path?: string,
     *   content?: string
     * } $options
     */
    public function addStyleTag(array $options): self
    {
        if (! isset($options['url']) && ! isset($options['path']) && ! isset($options['content'])) {
            throw new \InvalidArgumentException('Style tag must have either url, path, or content');
        }

        if (! isset($this->options['addStyleTag'])) {
            $this->options['addStyleTag'] = [];
        }

        $this->options['addStyleTag'][] = $options;

        return $this;
    }
}
