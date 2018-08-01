<?php
namespace Tests\Unit\Migration;

use PHPUnit\Framework\TestCase;
use SDAM\Config;

class ConfigTest extends TestCase
{

	/**
	 * @var string[]
	 */
	private $databaseConfig = [
       'dbname'   => 'autoMigrate',
       'user'     => 'root',
       'password' => 'root',
       'host'     => 'localhost',
       'driver'   => 'pdo_mysql',
	];

	/**
	 * @test
	 */
	public function testDatabaseConfigWithoutEnvFile()
	{
		$config = Config::current();
		$config->configure([Config::DATABASE => $this->databaseConfig]);

		$this->assertEquals('autoMigrate', $config->params[Config::DATABASE]['dbname']);
		$this->assertEquals('localhost',   $config->params[Config::DATABASE]['host']);
	}

}