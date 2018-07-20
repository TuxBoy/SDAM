<?php
namespace UnitTest\Fixtures;

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