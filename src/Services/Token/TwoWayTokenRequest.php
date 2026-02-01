<?php

namespace Mrmr7\LaravelShahin\Services\Token;

use Illuminate\Http\Client\Response;
use Mrmr7\LaravelShahin\Services\Request;

class TwoWayTokenRequest extends Request
{
    public string $endPoint = 'obh/oauth/token';

    protected int $port = 28453;

    public function __construct(private $bank, private $clientId, private $clientSecret)
    {
        parent::__construct();
    }

    public function getParams(): array
    {
        return [
            'grant_type' => 'client_credentials',
            'bank' => $this->bank,
        ];
    }

    public function prepare(\Illuminate\Http\Client\Factory|\Illuminate\Http\Client\PendingRequest|\Illuminate\Http\Client\Pool $http): \Illuminate\Http\Client\Factory|\Illuminate\Http\Client\PendingRequest|\Illuminate\Http\Client\Pool
    {
        parent::prepare($http);
        $http->withBasicAuth($this->clientId, $this->clientSecret);

        return $http;
    }

    public function successResponseCondition(Response $response): bool
    {
        return $response->successful() && $response->json('access_token');
    }
}
