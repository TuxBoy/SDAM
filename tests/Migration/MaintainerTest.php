<?php
namespace UnitTest\Migration;

use UnitTest\Fixtures\Category;
use UnitTest\Fixtures\FakeEntity;

class MaintainerTest extends \PHPUnit\Framework\TestCase
{

    public function testMaintainerConstruct()
    {
        $maintainer = new \SDAM\Maintainer([FakeEntity::class]);
        $this->assertCount(1, $maintainer->entities);
    }

    /*public function testParseForeignKey()
    {
        $maintainer = new \SDAM\Maintainer([FakeEntity::class]);
        $this->assertEquals('category_id', $maintainer->classToForeignKey(Category::class));
    }*/

}