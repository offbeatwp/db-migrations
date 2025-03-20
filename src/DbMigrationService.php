<?php

namespace OffbeatWP\DbMigrations;

use OffbeatWP\DbMigrations\Console\DbMigrationCommand;
use OffbeatWP\Services\AbstractService;

final class DbMigrationService extends AbstractService
{
    public function register(): void
    {
        if (offbeat('console')::isConsole()) {
            offbeat('console')->register(DbMigrationCommand::class);
        }
    }
}
