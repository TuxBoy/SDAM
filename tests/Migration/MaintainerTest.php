<?php
namespace UnitTest\Migration;

use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\TestCase;
use SDAM\Config;
use SDAM\Maintainer;
use UnitTest\Fixtures\FakeEntity;
use UnitTest\Fixtures\Simple;

class MaintainerTest extends TestCase
{

	public function setUp()
	{
		parent::setUp();
		Config::current()->configure([
			Config::DATABASE => ['url' => 'sqlite:///:memory:']
		]);
	}

	public function testMaintainerConstruct()
    {
        $maintainer = new Maintainer([FakeEntity::class]);
        $this->assertCount(1, $maintainer->entities);
    }

    public function testAddPrimaryKey()
	{
		$maintainer = new Maintainer([FakeEntity::class]);
		$maintainer->run();

		$schemaManager = $maintainer->connection->getSchemaManager();
		$columns = $schemaManager->listTableColumns('fakes');
		$this->assertTrue(array_key_exists('id', $columns));
	}

    public function testAddSimpleColumn()
	{
		$maintainer = new Maintainer([Simple::class]);
		$maintainer->run();

		$schemaManager = $maintainer->connection->getSchemaManager();
		$columns = $schemaManager->listTableColumns('simples');
		$this->assertTrue(array_key_exists('name', $columns));

		$this->assertEquals('string', $columns['name']->getType()->getName());
	}

    public function testAddBooleanColumn()
	{
		$maintainer = new Maintainer([FakeEntity::class]);
		$maintainer->run();

		$schemaManager = $maintainer->connection->getSchemaManager();
		$columns = $schemaManager->listTableColumns('fakes');
		$this->assertTrue(array_key_exists('online', $columns));

		$this->assertEquals(Type::BOOLEAN, $columns['online']->getType()->getName());
	}

}