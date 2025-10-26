<?php

namespace Mrmr7\LaravelShahin\Contracts;

interface TokenStorageInterface
{
    public function get(string $bank): mixed;

    public function set(string $bank, array $tokenInfo, int $ttl = 7200): void;

    public function forget(string $bank): void;

    public function has(string $bank): bool;
}
