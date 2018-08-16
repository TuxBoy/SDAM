<?php
namespace SDAM;

use Dotenv\Dotenv;

/**
 * Configuration
 *
 * Experimental class
 *
 * @example
 *  \SDAM\Config::DATABASE => [
 *      'dbname'   => 'autoMigrate',
 *      'user'     => 'root',
 *      'password' => 'root',
 *      'host'     => 'localhost',
 *      'driver'   => 'pdo_mysql',
 *  ],
 *  \SDAM\Config::ENTITY_PATH     => 'App\Model\\',
 *  \SDAM\Config::AUTO_DROP_FIELD => false
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

    const ENV_FILE = 'env_file';

    /**
     * @var self
     */
    public static $current;

    /**
     * @var string[]
     */
    private $params = [];

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
		$envFile      = $this->params[static::ENV_FILE] ?? null;
		if ($envFile) {
			(new Dotenv($envFile))->load();
		}
    }

	/**
	 * Returns all the configuration created via the method self::configure
	 *
	 * @return string[]
	 */
    public function getParams(): array
	{
		// Without .env file
		if (!isset($this->params[static::ENV_FILE])) {
			return $this->params;
		}
		if (getenv('DATABASE_URL')) {
			$databaseConfig = ['url' => getenv('DATABASE_URL')];
		} else {
			$databaseConfig = [
				'dbname'   => getenv('DB_DATABASE') ? getenv('DB_DATABASE') : 'test',
				'user'     => getenv('DB_USERNAME') ? getenv('DB_USERNAME') : 'root',
				'password' => getenv('DB_PASSWORD') ? getenv('DB_PASSWORD') : 'root',
				'host'     => getenv('DB_HOST')     ? getenv('DB_HOST')     : 'localhost',
				'driver'   => (getenv('DB_CONNECTION') === 'mysql') ? 'pdo_mysql' : 'mysql',
			];
		}
		$params = [static::DATABASE => $databaseConfig];
		return array_merge($params, $this->params);
	}

}