<?php

namespace OffbeatWP\DbMigrations;

use OffbeatWP\DbMigrations\Console\DbMigrationCommand;
use OffbeatWP\Services\AbstractService;
use OffbeatWP\Support\Wordpress\Console;

final class DbMigrationService extends AbstractService
{
    public function register(Console $console): void
    {
        if ($console::isConsole()) {
            $console->register(DbMigrationCommand::class);
        }
    }
}
