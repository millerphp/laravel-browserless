<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Features\Concerns;

trait HasCookieManagement
{
    /**
     * Set cookies for the page.
     *
     * @param array<array{
     *   name: string,
     *   value: string,
     *   domain?: string,
     *   path?: string,
     *   expires?: int,
     *   size?: int,
     *   httpOnly?: bool,
     *   secure?: bool,
     *   session?: bool,
     *   sameSite?: 'Lax'|'None'|'Strict',
     *   priority?: 'Low'|'Medium'|'High',
     *   sameParty?: bool,
     *   sourceScheme?: 'Unset'|'Secure'|'NonSecure',
     *   partitionKey?: string|array{
     *     sourceOrigin: string,
     *     hasCrossSiteAncestor?: bool
     *   },
     *   partitionKeyOpaque?: bool
     * }> $cookies
     */
    public function cookies(array $cookies): self
    {
        foreach ($cookies as $cookie) {
            if (! isset($cookie['name']) || ! isset($cookie['value'])) {
                throw new \InvalidArgumentException('Cookie must have name and value');
            }

            if (isset($cookie['sameSite']) && ! in_array($cookie['sameSite'], ['Lax', 'None', 'Strict'])) {
                throw new \InvalidArgumentException('Invalid sameSite value');
            }

            if (isset($cookie['priority']) && ! in_array($cookie['priority'], ['Low', 'Medium', 'High'])) {
                throw new \InvalidArgumentException('Invalid priority value');
            }

            if (isset($cookie['sourceScheme']) && ! in_array($cookie['sourceScheme'], ['Unset', 'Secure', 'NonSecure'])) {
                throw new \InvalidArgumentException('Invalid sourceScheme value');
            }
        }

        $this->options['cookies'] = $cookies;

        return $this;
    }

    /**
     * Add a single cookie.
     *
     * @param array{
     *   name: string,
     *   value: string,
     *   domain?: string,
     *   path?: string,
     *   expires?: int,
     *   size?: int,
     *   httpOnly?: bool,
     *   secure?: bool,
     *   session?: bool,
     *   sameSite?: 'Lax'|'None'|'Strict',
     *   priority?: 'Low'|'Medium'|'High',
     *   sameParty?: bool,
     *   sourceScheme?: 'Unset'|'Secure'|'NonSecure',
     *   partitionKey?: string|array{
     *     sourceOrigin: string,
     *     hasCrossSiteAncestor?: bool
     *   },
     *   partitionKeyOpaque?: bool
     * } $cookie
     */
    public function addCookie(array $cookie): self
    {
        if (! isset($this->options['cookies'])) {
            $this->options['cookies'] = [];
        }

        if (! isset($cookie['name']) || ! isset($cookie['value'])) {
            throw new \InvalidArgumentException('Cookie must have name and value');
        }

        if (isset($cookie['sameSite']) && ! in_array($cookie['sameSite'], ['Lax', 'None', 'Strict'])) {
            throw new \InvalidArgumentException('Invalid sameSite value');
        }

        if (isset($cookie['priority']) && ! in_array($cookie['priority'], ['Low', 'Medium', 'High'])) {
            throw new \InvalidArgumentException('Invalid priority value');
        }

        if (isset($cookie['sourceScheme']) && ! in_array($cookie['sourceScheme'], ['Unset', 'Secure', 'NonSecure'])) {
            throw new \InvalidArgumentException('Invalid sourceScheme value');
        }

        $this->options['cookies'][] = $cookie;

        return $this;
    }
}
