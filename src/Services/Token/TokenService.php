<?php

namespace Mrmr7\LaravelShahin\Services\Token;

use Mrmr7\LaravelShahin\Contracts\ShahinService;
use Mrmr7\LaravelShahin\Contracts\TokenStorageInterface;
use Mrmr7\LaravelShahin\Exceptions\ShahinTokenNotFoundException;
use Mrmr7\LaravelShahin\Facades\Shahin;

class TokenService extends ShahinService
{
    /**
     * Generate Token
     */
    public function getTwoWayToken($bank = null, $clientId = null, $clientSecret = null): array
    {
        $userCredentials = Shahin::getUserCredentials();
        $clientId = $clientId ?? $userCredentials['client_id'] ?? config('shahin.client_id');
        $clientSecret = $clientSecret ?? $userCredentials['client_secret'] ?? config('shahin.client_secret');

        return $this->sendRequest(new TwoWayTokenRequest($bank, $clientId, $clientSecret));
    }

    /**
     * Retrieve Saved token or create if need
     */
    public function getToken($bank = null, $clientId = null, $clientSecret = null, $createIfNotExists = true): string
    {
        $tokenStorage = app(TokenStorageInterface::class);
        $bank = $bank ?? Shahin::bank();
        if (! $tokenStorage->has($bank) && $createIfNotExists) {
            $this->setToken($bank, $clientId, $clientSecret);
        }
        $tokenData = $tokenStorage->get($bank);

        return $tokenData['access_token'] ?? '';
    }

    /**
     * Set token
     */
    public function setToken($bank = null, $clientId = null, $clientSecret = null): void
    {
        $tokenStorage = app(TokenStorageInterface::class);
        $tokenInfo = $this->getTwoWayToken($bank, $clientId, $clientSecret);
        $tokenStorage->set($bank, $tokenInfo, $tokenInfo['expires_in']);
    }

    public function getUsername(): string
    {
        return $this->getValueFromToken('user_name') ?? '';
    }

    public function getAccounts(): array
    {
        return $this->getValueFromToken('accounts') ?? [];
    }

    private function getValueFromToken($key)
    {
        $tokenStorage = app(TokenStorageInterface::class);
        $bank = Shahin::bank();

        if (! $tokenStorage->has($bank)) {
            $this->setToken($bank);
        }
        $tokenData = $tokenStorage->get($bank);

        if ($tokenData) {
            return $tokenData[$key];
        }

        throw new ShahinTokenNotFoundException('Token Not Set');
    }
}
