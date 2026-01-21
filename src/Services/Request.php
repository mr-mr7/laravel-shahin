<?php

namespace Mrmr7\LaravelShahin\Services;

use Illuminate\Http\Client\Response;

/**
 * هر رکوئستی که قراره سمت سامانه شاهین ارسال بشه باید از این کلاس اکستند بشه
 * EXP: AccountStatementRequest, TwoWayTokenRequest
 */
abstract class Request
{
    public string $method = 'POST';

    protected int $port = 38453;

    protected string $version = '0.3';

    protected bool $isSandbox;

    public string $endPoint;

    protected array $headers = [];

    protected array $body = [];

    protected array $params = [];

    public function __construct()
    {
        $this->isSandbox = config('shahin.sandbox');
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    // Override this method to prepare request before send
    public function prepare(\Illuminate\Http\Client\Factory|\Illuminate\Http\Client\PendingRequest|\Illuminate\Http\Client\Pool $http): \Illuminate\Http\Client\Factory|\Illuminate\Http\Client\PendingRequest|\Illuminate\Http\Client\Pool
    {
        return $http;
    }

    public function successResponseCondition(Response $response): bool
    {
        return $response->successful() && $response->json('transactionState') == 'SUCCESS';
    }

    public function port(): string|int
    {
        return $this->port;
    }

    public function version(): string
    {
        return $this->version;
    }
}
