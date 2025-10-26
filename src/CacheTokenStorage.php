<?php

namespace Mrmr7\LaravelShahin;

use Illuminate\Support\Facades\Cache;
use Mrmr7\LaravelShahin\Contracts\TokenStorageInterface;
use Mrmr7\LaravelShahin\Facades\Shahin;

class CacheTokenStorage implements TokenStorageInterface
{
    protected string $prefixKey = 'shahin_token';

    private array $tokens = [];

    public function __construct() {}

    public function get(string $bank): mixed
    {
        $key = $this->key($bank);
        $this->tokens[$key] = $this->tokens[$key] ?? Cache::get($key);

        return $this->tokens[$key] ?? Cache::get($key);
    }

    public function set(string $bank, array $tokenInfo, int $ttl = 7200): void
    {
        Cache::put($this->key($bank), $tokenInfo, $ttl);
    }

    public function forget(string $bank): void
    {
        $key = $this->key($bank);
        unset($this->tokens[$key]);
        Cache::forget($key);
    }

    public function has(string $bank): bool
    {
        $key = $this->key($bank);

        return isset($this->tokens[$key]) || Cache::has($key);
    }

    public function key($bank): string
    {
        if ($userIdentifier = Shahin::getUserIdentifier()) {
            $env = config('app.env');
            $hash = hash_hmac('sha256', (string) $userIdentifier, config('app.key'));
            $this->prefixKey = "$env:shahin_token:{$hash}";
        }

        return $this->prefixKey.':'.$bank;
    }
}
