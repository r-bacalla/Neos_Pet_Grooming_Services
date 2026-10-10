# Operations

## Scheduled jobs

The application schedules expired-batch write-offs daily at 00:10 and database/upload backups daily at 01:30. Laravel's scheduler must run for either job to execute.

On Linux, install the standard once-per-minute scheduler cron entry:

```cron
* * * * * cd /path/to/neos_pet_grooming && php artisan schedule:run >> /dev/null 2>&1
```

On Windows, use Task Scheduler to run `php artisan schedule:run` once per minute from the project directory, or keep `php artisan schedule:work` running under a process manager. Confirm registrations with `php artisan schedule:list`.

## Backups

`php artisan backup:run` writes a timestamped `.tar.gz` archive containing a MySQL/MariaDB dump, grooming waiver photos, and public uploads. Archives are stored under `backups/` on the disk configured by `BACKUP_DISK` and older archives are removed after `BACKUP_RETENTION_DAYS` (30 by default).

Configure these environment values as needed:

- `BACKUP_DISK`: a Laravel filesystem disk; use a private local disk for development and a separately credentialed off-site disk such as S3 for production.
- `BACKUP_MYSQLDUMP_BINARY`: `mysqldump` when it is on `PATH`, or its full path (for example, `C:\\xampp\\mysql\\bin\\mysqldump.exe`).
- `BACKUP_TAR_BINARY`: `tar` when it is on `PATH`, or its full path.
- `BACKUP_RETENTION_DAYS`: number of days to retain archives.

Archives contain customer and pet information. Keep the destination private, use encrypted off-site storage for production, and separately protect the application's `.env` secrets. Do not commit backup archives to source control.

To restore, download an archive from the configured disk, extract it to a private temporary directory, restore `database.sql` into the configured MySQL database using an authorized database account, then restore the archived `storage/private/grooming-waivers` and `storage/public` files to `storage/app/private/grooming-waivers` and `storage/app/public`. Test restores periodically in a non-production environment.

## Expiry batches

Each stock-in batch can record its own expiry date. Allocation and checkout use the earliest unexpired batch first (FEFO); the daily write-off zeros only expired batch balances and records each write-off in stock movement and audit history. Product rows created before batch tracking are migrated into a legacy batch using their existing expiry date.
