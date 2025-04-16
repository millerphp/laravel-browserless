<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Features\Concerns;

trait HasAuthentication
{
    /**
     * Set authentication credentials.
     *
     * @param  string|array{username: string, password: string}  $username  The username or an array of credentials
     * @param  string|null  $password  The password (required if $username is not an array)
     *
     * @throws \InvalidArgumentException If credentials are invalid
     */
    public function authenticate(string|array $username, ?string $password = null): self
    {
        if (is_string($username)) {
            if ($password === null) {
                throw new \InvalidArgumentException('Password is required when username is a string');
            }
            $this->options['authentication'] = [
                'username' => $username,
                'password' => $password,
            ];

            return $this;
        }

        $this->options['authentication'] = $username;

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
