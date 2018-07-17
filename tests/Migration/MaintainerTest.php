<?php
namespace UnitTest\Migration;

use UnitTest\Fixtures\FakeEntity;

class MaintainerTest extends \PHPUnit\Framework\TestCase
{

    public function testMaintainerConstruct()
    {
        $maintainer = new \TuxBoy\Maintainer([FakeEntity::class]);
        $this->assertCount(1, $maintainer->entities);
    }

}