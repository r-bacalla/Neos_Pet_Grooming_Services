<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ClearBusinessData extends Command
{
    protected $signature = 'app:clear-business-data {--force : Confirm permanent deletion of local business records}';

    protected $description = 'Clear local business records while preserving accounts and settings';

    private const BUSINESS_TABLES = [
        'sale_items',
        'sales',
        'loyalty_vouchers',
        'stock_movements',
        'audit_logs',
        'grooming_appointments',
        'grooming_visit_groups',
        'product_stock_batches',
        'service_addon_products',
        'grooming_addon_service',
        'service_addons',
        'grooming_addons',
        'grooming_prices',
        'pets',
        'customers',
        'products',
        'categories',
        'services',
        'generated_reports',
    ];

    public function handle(): int
    {
        if (!app()->environment('local')) {
            $this->error('This command only runs in the local environment.');

            return self::FAILURE;
        }

        if (!in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->error('This command requires the local MySQL or MariaDB database.');

            return self::FAILURE;
        }

        if (!$this->option('force')) {
            $this->error('No data was changed. Rerun with --force to permanently clear local business data.');

            return self::FAILURE;
        }

        foreach (self::BUSINESS_TABLES as $table) {
            if (!DB::getSchemaBuilder()->hasTable($table)) {
                $this->error("Required business table [{$table}] does not exist; no data was changed.");

                return self::FAILURE;
            }
        }

        $connection = DB::connection();
        $disk = Storage::disk('local');
        $reportFiles = count($disk->allFiles('reports'));
        $waiverFiles = count($disk->allFiles('grooming-waivers'));

        try {
            $connection->statement('SET FOREIGN_KEY_CHECKS=0');

            foreach (self::BUSINESS_TABLES as $table) {
                $count = $connection->table($table)->count();
                $connection->table($table)->truncate();
                $this->line("Cleared {$table}: {$count} rows");
            }
        } catch (Throwable $exception) {
            $this->error('Business data cleanup stopped: ' . $exception->getMessage());

            return self::FAILURE;
        } finally {
            $connection->statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $disk->deleteDirectory('reports');
        $disk->deleteDirectory('grooming-waivers');

        $this->info('Business data cleanup complete.');
        $this->line("Removed {$reportFiles} generated report files and {$waiverFiles} waiver photos.");
        $this->line('Preserved users, settings, sessions, cache, queues, and migrations.');

        return self::SUCCESS;
    }
}