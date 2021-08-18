<?php
namespace OffbeatWP\DbMigrations\Console;

use Exception;
use OffbeatWP\Console\AbstractCommand;
use OffbeatWP\DbMigrations\Phinx\WpPhinxApplication;
use Phinx\Config\Config;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class DbMigrationCommand extends AbstractCommand
{
    const COMMAND = 'db:migrations';

    public function execute($args, $argsNamed)
    {
        $consoleApp = new WpPhinxApplication();

        if (!empty($args)) {
            $command = $args[0];
            $commandObj = $consoleApp->find($command);

            if (is_callable([$commandObj, 'setConfig'])) {
                $commandObj->setConfig(new Config($this->getConfig()));
            }

        } else {
            $commandObj = $consoleApp;
        }

        $arguments = $this->getArguments($commandObj, $command, $args, $argsNamed);

        try {
            $input = new ArrayInput($arguments);
            $output = new ConsoleOutput();
            $commandObj->run($input, $output);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    private function getArguments($commandObj, $command, $args = [], $argsNamed = [])
    {
        $arguments = [];

        switch ($command) {
            case 'create':
            case 'seed:create':
                $arguments['name'] = $args[1] ?? null;
                break;
        }
        $arguments['command'] = $args[0];

        if (!empty($argsNamed)) foreach ($argsNamed as $key => $value) {
            $arguments['--' . $key] = $value;
        }
        
        return $arguments;
    }

    private function getConfig() : array
    {
        global $wpdb;

        $dbHost = defined('DB_HOST') ? DB_HOST : 'localhost';
        $dbHost = explode(':', $dbHost);

        $migrationsPath = get_template_directory() . '/db/migrations';
        $seedsPath = get_template_directory() . '/db/seeds';

        $this->mkDir($migrationsPath);
        $this->mkDir($seedsPath);

        return [
            'paths' => [
                'migrations' => $migrationsPath,
                'seeds' => $seedsPath,
            ],
            'environments' => [
                'default_migration_table' => $wpdb->prefix . 'db_migrations',
                'default_environment' => 'wp',
                'wp' => [
                    'adapter' => 'mysql',
                    'host' => $dbHost[0],
                    'name' => defined('DB_NAME') ? DB_NAME : null,
                    'user' => defined('DB_USER') ? DB_USER : null,
                    'pass' => defined('DB_PASSWORD') ? DB_PASSWORD : null,
                    'port' => $dbHost[1] ?? 3306,
                    'charset' => 'utf8',
                ],
            ],
            'version_order' => 'creation'
        ];
    }

    private function mkDir($path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
}