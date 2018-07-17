<?php
namespace UnitTest\Annotation;

use TuxBoy\Annotation\Annotation;
use UnitTest\Fixtures\Post;

class AnnotationTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @param string $annotationName
     * @param string $entity
     * @return Annotation
     * @throws \ReflectionException
     */
    private function makeAnnotationClass(string $annotationName, string $entity): Annotation
    {
        $class = new \ReflectionClass($entity);
        return new Annotation($class, $class->getProperty($annotationName)->getName());
    }

    public function testGetVarAnnotationValue()
    {
        $annotation = $this->makeAnnotationClass('name', Post::class);
        $this->assertEquals('string', $annotation->getAnnotation('var')->getValue());
    }

    public function testGetLengthAnnotationName()
    {
        $annotation = $this->makeAnnotationClass('name', Post::class);
        $this->assertTrue($annotation->hasAnnotation('length'));
        $this->assertEquals('length', $annotation->getAnnotation('length')->getName());
    }

    public function testGetLengthAnnotationValue()
    {
        $annotation = $this->makeAnnotationClass('name', Post::class);
        $this->assertTrue($annotation->hasAnnotation('length'));
        $this->assertEquals(255, $annotation->getAnnotation('length')->getValue());
    }

    public function testDefaultAnnotation()
    {
        $annotation = $this->makeAnnotationClass('name', Post::class);
        $this->assertTrue($annotation->hasAnnotation('default'));
        $this->assertEquals('default', $annotation->getAnnotation('default')->getName());
        $this->assertEquals('defaultName', $annotation->getAnnotation('default')->getValue());
    }

}