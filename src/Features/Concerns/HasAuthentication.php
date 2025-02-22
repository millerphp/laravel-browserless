<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Features\Concerns;

trait HasAuthentication
{
    /**
     * Set HTTP authentication credentials.
     *
     * @param  array{username: string, password: string}  $credentials
     */
    public function authenticate(array $credentials): self
    {
        if (! isset($credentials['username']) || ! isset($credentials['password'])) {
            throw new \InvalidArgumentException('Credentials must have username and password');
        }

        $this->options['authenticate'] = $credentials;

        return $this;
    }

    /**
     * Set HTTP Basic authentication credentials.
     */
    public function basicAuth(string $username, string $password): self
    {
        return $this->authenticate([
            'username' => $username,
            'password' => $password,
        ]);
    }
}
