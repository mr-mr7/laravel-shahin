<?php

namespace Mrmr7\LaravelShahin\Services\Token;

use Mrmr7\LaravelShahin\Contracts\Request;

class TwoWayTokenRequest extends Request
{
    public string $endPoint = 'v0.3/obh/oauth/token';

    public function __construct(private $bank, private $clientId, private $clientSecret)
    {
        parent::__construct();
        $this->port = config('shahin.port');
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
}
