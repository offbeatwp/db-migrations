<?php
namespace OffbeatWP\DbMigrations\Console;

use Exception;
use OffbeatWP\Console\AbstractCommand;
use OffbeatWP\DbMigrations\Phinx\WpPhinxApplication;
use Phinx\Config\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

final class DbMigrationCommand extends AbstractCommand
{
    public const COMMAND = 'db:migrations';

    /**
     * @param list<string> $args
     * @param array<non-empty-string, string> $argsNamed
     * @throws \Symfony\Component\Console\Exception\ExceptionInterface
     */
    public function execute(array $args, array $argsNamed): void
    {
        $command = $args[0] ?? '';
        $commandObj = $this->createCommandObject($command);
        $arguments = $this->getArguments($commandObj, $command, $args, $argsNamed);

        try {
            $input = new ArrayInput($arguments);
            $output = new ConsoleOutput();
            $commandObj->run($input, $output);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    private function createCommandObject(string $command): Command|WpPhinxApplication
    {
        $consoleApp = new WpPhinxApplication();

        if (!$command) {
            return $consoleApp;
        }

        $commandObj = $consoleApp->find($command);

        if (is_callable([$commandObj, 'setConfig'])) {
            $commandObj->setConfig(new Config($this->getConfig()));
        }

        return $commandObj;
    }

    /**
     * @param array<int, string> $args
     * @param array<non-empty-string, string> $argsNamed
     * @return array<non-empty-string, string|null>
     */
    private function getArguments(Command|WpPhinxApplication $commandObj, string $command, array $args = [], array $argsNamed = []): array
    {
        $arguments = [];

        if ($command === 'create' || $command === 'seed:create') {
            $arguments['name'] = $args[1] ?? null;
        }

        $arguments['command'] = $args[0];

        foreach ($argsNamed as $key => $value) {
            $arguments['--' . $key] = $value;
        }
        
        return $arguments;
    }

    /**
     * @return array{
     *      paths: array{
     *          migrations: string,
     *          seeds: string
     *      },
     *      environments: array{
     *          default_migration_table: string,
     *          default_environment: string,
     *          wp: array{
     *              adapter: string,
     *              host: string,
     *              name: string|null,
     *              user: string|null,
     *              pass: string|null,
     *              port: string|int,
     *              charset: string
     *          }
     *      },
     *      version_order: string
     *  }
     */
    private function getConfig() : array
    {
        global $wpdb;

        $dbHost = explode(':', defined('DB_HOST') ? DB_HOST : 'localhost');

        $migrationsPath = get_template_directory() . '/db/migrations';
        $seedsPath = get_template_directory() . '/db/seeds';

        $this->mkDir($migrationsPath);
        $this->mkDir($seedsPath);

        return [
            'paths' => [
                'migrations' => $migrationsPath,
                'seeds' => $seedsPath
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
                    'charset' => 'utf8'
                ],
            ],
            'version_order' => 'creation'
        ];
    }

    private function mkDir(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
}
