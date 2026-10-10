<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class RunApplicationBackup extends Command
{
    protected $signature = 'backup:run';

    protected $description = 'Back up the application database and uploaded files';

    public function handle(): int
    {
        $driver = DB::connection()->getDriverName();
        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            $this->error("Database backup supports MySQL/MariaDB; current driver is {$driver}.");

            return self::FAILURE;
        }

        $connection = config('database.connections.' . config('database.default'));
        $staging = storage_path('app/backup-staging/' . Str::uuid());
        File::ensureDirectoryExists($staging);

        try {
            $credentialsPath = $staging . DIRECTORY_SEPARATOR . 'mysql.cnf';
            File::put($credentialsPath, $this->mysqlCredentials($connection));
            @chmod($credentialsPath, 0600);

            $dumpPath = $staging . DIRECTORY_SEPARATOR . 'database.sql';
            $dump = fopen($dumpPath, 'wb');
            if (!$dump) {
                throw new RuntimeException('Unable to create the database dump file.');
            }

            $dumpProcess = new Process([
                config('backup.mysql_dump_binary'),
                '--defaults-extra-file=' . $credentialsPath,
                '--single-transaction',
                '--routines',
                '--triggers',
                '--databases',
                $connection['database'],
            ]);
            $dumpProcess->setTimeout(3600);
            $dumpErrors = '';
            $dumpProcess->run(function (string $type, string $buffer) use ($dump, &$dumpErrors) {
                if ($type === Process::OUT) {
                    fwrite($dump, $buffer);
                } else {
                    $dumpErrors .= $buffer;
                }
            });
            fclose($dump);

            if (!$dumpProcess->isSuccessful()) {
                throw new RuntimeException(trim($dumpErrors) ?: 'The MySQL dump process failed.');
            }

            $storagePath = $staging . DIRECTORY_SEPARATOR . 'storage';
            File::ensureDirectoryExists($storagePath);
            $waiverPhotos = storage_path('app/private/grooming-waivers');
            $publicUploads = storage_path('app/public');
            if (File::isDirectory($waiverPhotos)) {
                File::copyDirectory($waiverPhotos, $storagePath . DIRECTORY_SEPARATOR . 'private/grooming-waivers');
            }
            if (File::isDirectory($publicUploads)) {
                File::copyDirectory($publicUploads, $storagePath . DIRECTORY_SEPARATOR . 'public');
            }

            $filename = 'neos-pet-grooming-' . now()->format('Ymd-His') . '.tar.gz';
            $archivePath = $staging . DIRECTORY_SEPARATOR . $filename;
            $archiveProcess = new Process([
                config('backup.tar_binary'),
                '-czf',
                $archivePath,
                '-C',
                $staging,
                'database.sql',
                'storage',
            ]);
            $archiveProcess->setTimeout(3600);
            $archiveProcess->run();

            if (!$archiveProcess->isSuccessful()) {
                throw new RuntimeException(trim($archiveProcess->getErrorOutput()) ?: 'The archive process failed.');
            }

            $disk = Storage::disk(config('backup.disk'));
            $archive = fopen($archivePath, 'rb');
            if (!$archive) {
                throw new RuntimeException('Unable to read the completed backup archive.');
            }
            $stored = $disk->put('backups/' . $filename, $archive);
            fclose($archive);

            if (!$stored) {
                throw new RuntimeException('The backup could not be written to the configured storage disk.');
            }

            $cutoff = now()->subDays(max(1, (int) config('backup.retention_days')))->getTimestamp();
            foreach ($disk->files('backups') as $oldBackup) {
                if (str_starts_with(basename($oldBackup), 'neos-pet-grooming-') && $disk->lastModified($oldBackup) < $cutoff) {
                    $disk->delete($oldBackup);
                }
            }

            $this->info('Backup saved: ' . $filename);

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error('Backup failed: ' . $exception->getMessage());

            return self::FAILURE;
        } finally {
            File::deleteDirectory($staging);
        }
    }

    private function mysqlCredentials(array $connection): string
    {
        $escape = static fn (mixed $value): string => str_replace(
            ["\\", '"', "\r", "\n"],
            ["\\\\", '\\"', '', ''],
            (string) $value
        );

        return "[client]\n" .
            'host="' . $escape($connection['host'] ?? '127.0.0.1') . "\"\n" .
            'port="' . $escape($connection['port'] ?? 3306) . "\"\n" .
            'user="' . $escape($connection['username'] ?? '') . "\"\n" .
            'password="' . $escape($connection['password'] ?? '') . "\"\n";
    }
}
