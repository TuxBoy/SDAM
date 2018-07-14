<?php
namespace TuxBoy;

/**
 * Configuration
 *
 * Experimental class
 *
 * @example
 *  \TuxBoy\Config::DATABASE => [
 *      'dbname'   => 'autoMigrate',
 *      'user'     => 'root',
 *      'password' => 'root',
 *      'host'     => 'localhost',
 *      'driver'   => 'pdo_mysql',
 *  ],
 *  \TuxBoy\Config::ENTITY_PATH => 'App\Model\\'
 */
class Config
{

    const DATABASE = 'database';

    const ENTITY_PATH = 'entity_path';

    /**
     * @var self
     */
    public static $current;

    /**
     * @var string[]
     */
    public $params = [];

    /**
     * @return Config
     */
    public static function current(): self
    {
        if (!self::$current) {
            self::$current = new Config();
        }
        return self::$current;
    }

    /**
     * @param string[] $params
     */
    public function configure(array $params = []): void
    {
        $this->params = $params;
    }

}