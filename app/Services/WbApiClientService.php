<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\PendingRequest;

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

        /**  @var PendingRequest $pendingRequest */
        $pendingRequest = $this->http->retry(3, 500)->acceptJson();

        $response = $pendingRequest->get($this->baseUrl . "/api/{$resource}", $params)->throw()->json();


        return is_array($response) && array_key_exists('data', $response) ? ($response['data'] ?? []) : (is_array($response) ? $response : []);
    }
}
