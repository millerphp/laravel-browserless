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
     * Add a cookie.
     *
     * @param  string|array{name: string, value: string, domain?: string, path?: string, expires?: int, size?: int, httpOnly?: bool, secure?: bool}  $name  The cookie name or an array of cookie properties
     * @param  string|null  $value  The cookie value (required if $name is not an array)
     *
     * @throws \InvalidArgumentException If cookie data is invalid
     */
    public function addCookie(string|array $name, ?string $value = null): self
    {
        if (is_string($name)) {
            if ($value === null) {
                throw new \InvalidArgumentException('Value is required when name is a string');
            }
            $this->options['cookies'][] = [
                'name' => $name,
                'value' => $value,
            ];

            return $this;
        }

        $this->options['cookies'][] = $name;

        return $this;
    }

    /**
     * Set cookies.
     *
     * @param  array<array{name: string, value: string, domain?: string, path?: string, expires?: int, size?: int, httpOnly?: bool, secure?: bool}>  $cookies
     */
    public function setCookies(array $cookies): self
    {
        $this->options['cookies'] = $cookies;

        return $this;
    }
}
