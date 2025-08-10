<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\WbImportService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;

use function Laravel\Prompts\select;

class ImportWbData extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import {entity} {--from=} {--to=} {--limit=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data from wb API';

    /**
     * Execute the console command.
     */
    public function handle(WbImportService $service): int
    {
        $entity = $this->argument('entity');

        if ($entity === 'all')
        {
            $total = 0;

            $resources = config('wb.resources');
            foreach ($resources as $name => $resource)
            {
                $params = [
                    'from'  => $this->option('from'),
                    'to'    => $this->option('to'),
                    'limit' => $this->option('limit') ?? $resource['limit'],
                ];
                $count = $service->run($name, $resource['table'], array_filter($params, fn($v) => $v !== null));
                $total += $count;

                $this->info("Imported {$count} rows into {$resource['table']}");
            }


            $this->info("Total inserted across all: {$total}");
            return self::SUCCESS;
        }

        $resource = config("wb.resources.{$entity}");
        if (!$resource)
        {
            $this->error("Unknown entity: {$entity}");
            return self::FAILURE;
        }

        $params = [
            'from'  => $this->option('from'),
            'to'    => $this->option('to'),
            'limit' => $this->option('limit') ?? $resource['limit'],
        ];


        $count = $service->run($entity, $resource['table'], array_filter($params, fn($v) => $v !== null));

        $this->info("Imported {$count} rows into {$resource['table']}");
        return self::SUCCESS;
    }

    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'entity' => fn() => select(
                label: 'Which entity do you want to export?',
                options: array_merge(['all'], array_keys(config('wb.resources'))),
            ),
        ];
    }
}
