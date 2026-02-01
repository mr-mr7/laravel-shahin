<?php

namespace Mrmr7\LaravelShahin\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Mrmr7\LaravelShahin\Contracts\HasToken;
use Mrmr7\LaravelShahin\Exceptions\ShahinException;
use Mrmr7\LaravelShahin\Facades\Shahin;

/**
 * این کلاس مخصوص سرویس های اصلی سامانه میباشد که هر سرویس شامل یکسری رکوئست است
 * همه ی سرویس ها باید از این کلاس اکستند بشن
 *
 * EXP: AccountService, TokenService, ....
 */
abstract class ShahinService
{
    private string $baseUrl = '';

    private bool $isSandbox;

    public function __construct(private ?string $token = null)
    {
        $this->isSandbox = config('shahin.sandbox');
        $this->baseUrl = $this->isSandbox ? config('shahin.sandbox_base_url') : config('shahin.base_uri');
    }

    /**
     * Sends a request to the API server.
     *
     * @param  Request|Request[]  $request  The request to send.
     * @return mixed The response from API server.
     *
     * @throws ShahinException
     */
    public function sendRequest(Request|array $request, $responseKey = null): array
    {
        $multiRequest = is_array($request);
        $requests = is_array($request) ? $request : [$request];

        $poolResponses = Http::pool(function (Pool $pool) use ($requests, $multiRequest) {
            $poolRequests = [];
            $i = 0;
            foreach ($requests as $requestItem) {
                $method = $requestItem->method;
                $baseUrl = "$this->baseUrl:".$requestItem->port().'/v'.$requestItem->urlVersion();

                $key = $multiRequest && $requestItem->requestKey() ? $requestItem->requestKey() : $i++;

                $pendingRequest = $pool->as($key)->baseUrl($baseUrl);

                if ($this->isSandbox) {
                    $pendingRequest->withoutVerifying();
                }

                $pendingRequest = $requestItem->prepare($pendingRequest);
                if ($requestItem instanceof HasToken) {
                    $token = $this->token ?? Shahin::token()->getToken();
                    $pendingRequest->withToken($token);
                }

                $poolRequests[] = $pendingRequest
                    ->withHeaders(array_merge($this->signHeaders($method, $requestItem->urlVersion(), $requestItem->endPoint, $requestItem->getBody()), $requestItem->getHeaders()))
                    ->withQueryParameters($requestItem->getParams())
                    ->$method($requestItem->endPoint, $requestItem->getBody());
            }

            return $poolRequests;
        });

        if (! $multiRequest) {
            $response = Arr::first($poolResponses);
            if ($response instanceof ConnectionException) {
                throw $response;
            } elseif ($request->successResponseCondition($response)) {
                return $response->json($responseKey);
            }
            throw new ShahinException($response->body(), $response->status());
        }

        $responses = [];
        foreach ($poolResponses as $key => $poolResponse) {
            $relatedRequest = $request[$key];
            if ($poolResponse instanceof ConnectionException) {
                $responses['errors'][$key] = $poolResponse->getMessage();
            } elseif ($relatedRequest->successResponseCondition($poolResponse)) {
                $responses['data'][$key] = $poolResponse->json($responseKey);
            } else {
                $responses['errors'][$key] = $poolResponse->body();
            }
        }

        return $responses;
    }

    private function signHeaders($method, $urlVersion, $endpoint, $data): array
    {
        $uuid = Str::uuid()->toString();
        $year = date('Y');
        $clientId = config('shahin.client_id');
        $clientSecret = config('shahin.client_secret');
        $timestamp = time() * 1000;
        $key = $year.$clientId.$clientSecret;
        $keySign = hash('sha256', $key, 1);
        $dataFinalJson = json_encode($data, JSON_UNESCAPED_UNICODE);
        $dataPayload = $dataFinalJson;
        $dataPayload = str_replace('"', '', $dataPayload);
        $dataPayload = str_replace(':', '=', $dataPayload);
        $dataPayload = str_replace(' ', '', $dataPayload);
        $signBody = strtoupper(hash('sha256', $dataPayload));
        $canonical = $method."\n/".$urlVersion.$endpoint."\nx-obh-timestamp:$timestamp\nx-obh-uuid:$uuid\n\nx-obh-timestamp;x-obh-uuid\n$signBody";
        $string2sign = strtoupper(hash('sha256', $canonical));
        $finalSign = strtoupper(hash_hmac('sha256', $string2sign, $keySign, 0));

        return [
            'Content_Type' => 'application/json',
            'charset' => 'utf-8',
            'X-Obh-timestamp' => $timestamp,
            'X-Obh-uuid' => $uuid,
            'X-Obh-signature' => 'OBH1-HMAC-SHA256;SignedHeaders=X-Obh-uuid,X-Obh-timestamp;signature='.$finalSign,
        ];
    }
}
