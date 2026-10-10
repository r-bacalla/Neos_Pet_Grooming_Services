<?php

return [
    'disk' => env('BACKUP_DISK', env('FILESYSTEM_DISK', 'local')),
    'mysql_dump_binary' => env(
        'BACKUP_MYSQLDUMP_BINARY',
        PHP_OS_FAMILY === 'Windows' && file_exists('C:\\xampp\\mysql\\bin\\mysqldump.exe')
            ? 'C:\\xampp\\mysql\\bin\\mysqldump.exe'
            : 'mysqldump'
    ),
    'tar_binary' => env('BACKUP_TAR_BINARY', 'tar'),
    'retention_days' => (int) env('BACKUP_RETENTION_DAYS', 30),
];
