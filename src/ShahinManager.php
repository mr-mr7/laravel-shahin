<?php

namespace Mrmr7\LaravelShahin;

use Mrmr7\LaravelShahin\Services\Account\AccountService;
use Mrmr7\LaravelShahin\Services\Token\TokenService;

class ShahinManager
{
    protected array $services = [];

    private string $bank;

    private string|int|null $userIdentifier = null;

    private array $credentials = [];

    public function forUser(string|int $userIdentifier, array $credentials = []): self
    {
        $this->userIdentifier = $userIdentifier;
        $this->credentials = $credentials;

        return $this;
    }

    public function getUserCredentials(): array
    {
        return $this->credentials;
    }

    public function getUserIdentifier(): string|int|null
    {
        return $this->userIdentifier;
    }

    public function setBank($bank): self
    {
        $this->bank = $bank;

        return $this;
    }

    public function bank()
    {
        return $this->bank ?? config('shahin.bank');
    }

    public function account(): AccountService
    {
        return $this->getService(AccountService::class);
    }

    public function token(): TokenService
    {
        return $this->getService(TokenService::class);

    }

    protected function getService(string $class)
    {
        if (! isset($this->services[$class])) {
            $this->services[$class] = app($class);
        }

        return $this->services[$class];
    }
}
