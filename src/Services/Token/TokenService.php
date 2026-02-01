<?php

namespace Mrmr7\LaravelShahin\Services\Token;

use Mrmr7\LaravelShahin\Contracts\TokenStorageInterface;
use Mrmr7\LaravelShahin\Exceptions\ShahinTokenNotFoundException;
use Mrmr7\LaravelShahin\Facades\Shahin;
use Mrmr7\LaravelShahin\Services\ShahinService;

class TokenService extends ShahinService
{
    /**
     * Generate Token
     */
    public function getTwoWayToken($bank = null, $clientId = null, $clientSecret = null): array
    {
        $userCredentials = Shahin::getUserCredentials();
        $clientId = $clientId ?? $userCredentials['client_id'] ?? '';
        $clientSecret = $clientSecret ?? $userCredentials['client_secret'] ?? '';

        return $this->sendRequest(new TwoWayTokenRequest($bank, $clientId, $clientSecret));
    }

    /**
     * Retrieve Saved token or create if need
     */
    public function getToken($bank = null, $createIfNotExists = true): string
    {
        try {
            return $this->getValueFromToken('access_token', $bank, $createIfNotExists);
        } catch (ShahinTokenNotFoundException $exception) {
            return '';
        }
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

    private function getValueFromToken($key, $bank = null, $createIfNotExists = true)
    {
        $tokenStorage = app(TokenStorageInterface::class);
        $bank = $bank ?? Shahin::bank();

        if (! $tokenStorage->has($bank) && $createIfNotExists) {
            $this->setToken($bank);
        }
        $tokenData = $tokenStorage->get($bank);

        if ($tokenData) {
            return $tokenData[$key];
        }

        throw new ShahinTokenNotFoundException('Token Not Set');
    }
}
