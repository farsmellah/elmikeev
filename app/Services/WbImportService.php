<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\ConnectionInterface;

class WbImportService
{
    public function __construct(
        private readonly WbApiClientService $client,
        private readonly ConnectionInterface $db
    )
    {
    }

    public function run(string $resource, string $table, array $params): int
    {
        $totalRowsInserted = 0;


        $limit = $params['limit'] ?? 500;
        $page  = 1;


        $apiParams = match ($resource)
        {
            'stocks' => ['dateFrom' => now()->toDateString()],
            default  => [
                'dateFrom' => $params['from'] ?? now()->toDateString(),
                'dateTo'   => $params['to']   ?? now()->toDateString(),
            ]
        };

        do
        {
            $items = $this->client->fetch($resource, $apiParams, $page, $limit);
            if ($items)
            {
                $now  = now();

                $rows = array_map(function ($item) use ($now)
                {
                    $json = json_encode($item, JSON_UNESCAPED_UNICODE);
                    return [
                        'payload_hash' => md5($json),
                        'payload'      => $json,
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ];
                }, $items);

                $totalRowsInserted += $this->db->table($table)->insertOrIgnore($rows);
            }

            $page++;
        } while (count($items) === $limit);

        return $totalRowsInserted;
    }
}
