<?php
namespace UnitTest\DocReader;

use ReflectionProperty;
use UnitTest\Fixtures\FakeEntity;

class PhpDocReaderTest extends \PHPUnit\Framework\TestCase
{

    public function testGetVarAnnotation()
    {
        $reader = new \App\DocReader\PhpDocReader();
        $reflectionProperty = new ReflectionProperty(FakeEntity::class, 'name');
        $value = $reader->getAnnotation($reflectionProperty, 'var');
        $this->assertEquals('string', $value);
    }

    public function testGetVarAnnotationBoolVal()
    {
        $reader = new \App\DocReader\PhpDocReader();
        $reflectionProperty = new ReflectionProperty(FakeEntity::class, 'online');
        $value = $reader->getAnnotation($reflectionProperty, 'var');
        $this->assertEquals('boolean', $value);
    }

}