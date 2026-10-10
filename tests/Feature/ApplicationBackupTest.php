<?php

it('refuses to create a MySQL backup when running on an unsupported database driver', function () {
    $this->artisan('backup:run')
        ->expectsOutput('Database backup supports MySQL/MariaDB; current driver is sqlite.')
        ->assertExitCode(1);
});
