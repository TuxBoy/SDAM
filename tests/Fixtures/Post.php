<?php
namespace UnitTest\Fixtures;

/**
 * @storeName posts_test
 */
class Post
{

    /**
     * @default defaultName
     * @length 255
     * @var string
     */
    public $name;

    /**
     * @var boolean
     */
    public $draft;

    /**
     * @link belongsTo
     * @var Category
     */
    public $category;

}