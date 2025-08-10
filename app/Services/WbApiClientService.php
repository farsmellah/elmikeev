<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\Factory as HttpFactory;

class WbApiClientService
{
    public function __construct(
        private readonly HttpFactory $http,
        private string $baseUrl = '',
        private string $apiKey = ''
    )
    {
        $this->baseUrl = rtrim(env('WB_BASE_URL'), '/');
        $this->apiKey  = env('WB_API_KEY');
    }

    public function fetch(string $resource, array $params, int $page, int $limit): array
    {
        $params['key']   = $this->apiKey;
        $params['page']  = $page;
        $params['limit'] = $limit;

        $attempt = 0;
        $maxAttempts = 6;
        $baseSleepMs = 500;

        while (true)
        {
            $response = $this->http
                ->timeout(30)
                ->connectTimeout(5)
                ->acceptJson()
                ->get($this->baseUrl . "/api/{$resource}", $params);

            if ($response->successful())
            {
                $json = $response->json();

                if (is_array($json) && array_key_exists('data', $json))
                {
                    return [
                        'items' => $json['data'] ?? [],
                        'meta'  => $json['meta'] ?? null,
                    ];
                }
            }

            $status = $response->status();


            if ($status === 429)
            {
                $retryAfter = (int) ($response->header('Retry-After') ?? 0);
                $sleepMs = max($baseSleepMs * (2 ** $attempt), $retryAfter * 1000);

                $waitSec = number_format($sleepMs / 1000, 1, '.', '');
                $nextTry = $attempt + 1;
                echo "[WB API][{$resource} p={$page} lim={$limit}] 429 Too Many Requests → waiting {$waitSec}s (Retry-After={$retryAfter}s), retry #{$nextTry}/{$maxAttempts}\n";
            }
            else
            {
                echo "[WB API][{$resource} p={$page} lim={$limit}] {$status} Error → no retry, throwing...\n";
                $response->throw();
            }

            $attempt++;
            if ($attempt >= $maxAttempts)
            {
                echo "[WB API][{$resource} p={$page} lim={$limit}] retries exhausted ({$maxAttempts}), throwing...\n";
                $response->throw();
            }

            usleep($sleepMs * 1000);
        }
    }
}
