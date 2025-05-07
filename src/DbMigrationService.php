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

        add_action('rest_api_init', function () {
            register_rest_route('vollegrond', '/db/migrate', [
                'methods' => 'GET',
                'callback' => function () {
                    (new DbMigrationCommand())->execute(['migrate'], []);
                },
                'permission_callback' => function () {
                    $token = $this->getConst('VG_DB_MIGRATE_AUTHORIZATION_TOKEN');
                    return $token && filter_input(INPUT_SERVER, 'HTTP_AUTHORIZATION') === $token;
                }
            ]);
        });
    }

    private function getConst(string $name): ?string
    {
        return defined($name) ? constant($name) : null;
    }
}
