<?php
namespace UnitTest\Annotation;

use TuxBoy\Annotation\Annotation;
use UnitTest\Fixtures\Category;
use UnitTest\Fixtures\Post;

class AnnotationTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @param string $annotationName
     * @param string $entity
     * @param bool $property
     * @return Annotation
     * @throws \ReflectionException
     */
    private function makeAnnotationClass(string $annotationName, string $entity, bool $property = true): Annotation
    {
        $class        = new \ReflectionClass($entity);
        $propertyName = $property ? $class->getProperty($annotationName)->getName() : null;
        return new Annotation($class, $propertyName);
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

    public function testGetVarObjectWithNamespace()
    {
        $annotation = $this->makeAnnotationClass('category', Post::class);
        $className  = $annotation->getObjectVar();
        $this->assertEquals(Category::class, $className);
    }

    public function testGetClassAnnotation()
    {
        $annotation = $this->makeAnnotationClass('storeName', Post::class, false);
        $this->assertTrue($annotation->hasAnnotation('storeName'));
        $this->assertEquals('storeName', $annotation->getAnnotation('storeName')->getName());
        $this->assertEquals('posts_test', $annotation->getAnnotation('storeName')->getValue());
    }

}