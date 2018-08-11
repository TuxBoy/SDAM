<?php
namespace UnitTest\Migration;

use PHPUnit\Framework\TestCase;
use SDAM\Config;
use SDAM\EntityAdapter\EntityAdapter;
use UnitTest\Fixtures\Category;
use UnitTest\Fixtures\Foo;
use UnitTest\Fixtures\Post;
use UnitTest\Fixtures\Simple;

class EntityAdapterTest extends TestCase
{

	public function setUp()
	{
		parent::setUp();
		Config::current()->configure([Config::ENTITY_PATH => 'UnitTest\Fixtures']);
		if (!is_dir(dirname(__DIR__) . '/Entity')) {
			mkdir(dirname(__DIR__) . '/Entity');
		}
	}

	public function tearDown()
	{
		parent::tearDown();
		if (is_dir(dirname(__DIR__) . '/Entity')) {
			rmdir(dirname(__DIR__) . '/Entity');
		}
	}

	/**
	 * @return string[]
	 */
	public function entityProvider(): array
	{
		return [
			Foo::class      => [Foo::class],
			Post::class     => [Post::class],
			Category::class => [Category::class],
			Simple::class   => [Simple::class]
		];
	}

	/**
	 * @test
	 */
	public function find_all_entities()
	{
		$adapterEntity = new EntityAdapter(dirname(__DIR__) . '/Fixtures/');
		self::assertEquals(5, $adapterEntity->count());
	}

	/**
	 * @test
	 */
	public function find_all_in_empty_directory()
	{
		$adapterEntity = new EntityAdapter(dirname(__DIR__) . '/Entity');
		self::assertEquals(0, $adapterEntity->count());
	}

	/**
	 * @test
	 */
	public function find_with_ignored_entity()
	{
		$adapterEntity = new EntityAdapter(dirname(__DIR__) . '/Fixtures', Post::class);
		self::assertEquals(4, $adapterEntity->count());

		$adapterEntity = new EntityAdapter(dirname(__DIR__) . '/Fixtures', [Post::class, Category::class]);
		self::assertEquals(3, $adapterEntity->count());
	}

	/**
	 * @test
	 * @dataProvider entityProvider
	 */
	public function get_all_entities_with_array($entity)
	{
		$adapterEntity = new EntityAdapter(dirname(__DIR__) . '/Fixtures/');
		self::assertContains($entity, $adapterEntity->toArray());
	}

}