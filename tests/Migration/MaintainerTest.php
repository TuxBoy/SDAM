<?php
namespace UnitTest\Migration;

use UnitTest\Fixtures\Category;
use UnitTest\Fixtures\FakeEntity;
use UnitTest\Fixtures\Post;

class MaintainerTest extends \PHPUnit\Framework\TestCase
{

    public function testMaintainerConstruct()
    {
        $maintainer = new \TuxBoy\Maintainer([FakeEntity::class]);
        $this->assertCount(1, $maintainer->entities);
    }

    public function testGetTableName()
    {
        $maintainer = new \TuxBoy\Maintainer([Post::class]);
        $class      = new \ReflectionClass(Post::class);
        $categoryClass      = new \ReflectionClass(Category::class);
        $this->assertEquals('posts', $maintainer->getTableName($class->getShortName()));
        $this->assertEquals('categories', $maintainer->getTableName($categoryClass->getShortName()));
    }

}