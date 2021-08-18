<?php

namespace OffbeatWP\DbMigrations;

use OffbeatWP\DbMigrations\Console\DbMigrationCommand;
use OffbeatWP\Services\AbstractService;

class Service extends AbstractService
{
    public function register()
    {
        $this->registerConsole();
    }

    public function registerConsole()
    {
        if (offbeat('console')->isConsole()) {
            offbeat('console')->register(DbMigrationCommand::class);
        }
    }
}
