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

	public function setUp()
	{
		parent::setUp();
		file_put_contents('./.env', "");
	}

	public function tearDown()
	{
		parent::tearDown();
		if (file_exists('./.env')) {
			unlink('./.env');
		}
	}

	/**
	 * @test
	 */
	public function testDatabaseConfigWithoutEnvFile()
	{
		$config = Config::current();
		$config->configure([Config::DATABASE => $this->databaseConfig]);

		$this->assertEquals('autoMigrate', $config->getParams()[Config::DATABASE]['dbname']);
		$this->assertEquals('localhost',   $config->getParams()[Config::DATABASE]['host']);
	}

	/**
	 * @test
	 */
	public function testDataBaseConfigWithEnvFile()
	{
		putenv("DB_DATABASE=test");
		putenv("DB_CONNECTION=mysql");
		$config = Config::current();
		$config->configure([Config::ENV_FILE => dirname(dirname(__DIR__))]);
		self::assertTrue(file_exists('./.env'));

		self::assertEquals('test',      $config->getParams()[Config::DATABASE]['dbname']);
		self::assertEquals('pdo_mysql', $config->getParams()[Config::DATABASE]['driver']);
	}

	/**
	 * @test
	 */
	public function testFullConfig()
	{
		putenv("DB_DATABASE=test");
		putenv("DB_CONNECTION=mysql");
		$config = Config::current();
		$config->configure([
			Config::ENV_FILE        => dirname(dirname(__DIR__)),
			Config::ENTITY_PATH     => 'App\\Entity',
			Config::AUTO_DROP_FIELD => false
		]);
		self::assertTrue(file_exists('./.env'));
		$expected = [
			Config::DATABASE        => ['dbname' => 'test', 'user' => 'root', 'password' => 'root', 'host' => 'localhost', 'driver' => 'pdo_mysql'],
			Config::ENV_FILE        => dirname(dirname(__DIR__)),
			Config::ENTITY_PATH     => 'App\\Entity',
			Config::AUTO_DROP_FIELD => false
		];
		self::assertEquals($expected, $config->getParams());
	}

	/**
	 * @test
	 */
	public function config_with_env_file_and_database_url()
	{
		putenv('DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name');
		$config = Config::current();
		$config->configure([Config::ENV_FILE => dirname(dirname(__DIR__))]);
		self::assertTrue(file_exists('./.env'));

		self::assertEquals(
			'mysql://db_user:db_password@127.0.0.1:3306/db_name',
			$config->getParams()[Config::DATABASE]['url']
		);
	}

}