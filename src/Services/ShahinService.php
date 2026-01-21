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

        $poolResponses = Http::pool(function (Pool $pool) use ($requests) {
            $poolRequests = [];
            $i = 0;
            foreach ($requests as $requestItem) {
                $method = $requestItem->method;
                $baseUrl = "$this->baseUrl:".$requestItem->port()."/v{$requestItem->version}";

                $key = class_basename($requestItem).'-'.$i++;

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
                    ->withHeaders(array_merge([
                        'Content_Type' => 'application/json',
                        'X-Obh-timestamp' => now()->getTimestampMs(),
                        'X-Obh-uuid' => Str::uuid()->toString(),
                        'X-Obh-signature' => $this->sign(),
                    ], $requestItem->getHeaders()))
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
            $relatedRequest = $request[explode('-', $key)[1]];
            if ($poolResponse instanceof ConnectionException) {
                $responses['errors'][$key] = $poolResponse->getMessage();
            } elseif ($relatedRequest->successResponseCondition($poolResponse)) {
                $responses['data'][] = $poolResponse->json($responseKey);
            } else {
                $responses['errors'][$key] = $poolResponse->body();
            }
        }

        return $responses;
    }

    private function sign()
    {
        return 'OBH1-HMAC-SHA256;SignedHeaders=X-Obh-uuid,X-Obh-timestamp;Signature=77076581D9CA5A5F99A1020BBBD4E113B6CAA41BEE22B9665670793625FF244';
    }
}
