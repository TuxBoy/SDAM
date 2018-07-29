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
 *  \TuxBoy\Config::ENTITY_PATH     => 'App\Model\\',
 *  \TuxBoy\Config::AUTO_DROP_FIELD => false
 */
class Config
{

    /**
     * Doctrine dbal config
     *
     * @string[]
     * @see https://www.doctrine-project.org/projects/doctrine-dbal/en/2.7/reference/configuration.html
     */
    const DATABASE = 'database';

    /**
     * Path or namespace of entities class
     *
     * @var string
     */
    const ENTITY_PATH = 'entity_path';

    /**
     * If this option is false, the fields will not be deleted in base if its property has been removed
     * It will have to be done manually
     *
     * @var boolean
     */
    const AUTO_DROP_FIELD = 'auto_drop_field';

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