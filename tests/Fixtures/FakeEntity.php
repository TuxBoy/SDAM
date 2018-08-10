<?php
namespace UnitTest\Fixtures;

/**
 * Class FakeEntity
 * @storeName fakes
 */
class FakeEntity
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var boolean
     */
    public $online;

	/**
	 * @link belongsTo
	 * @var Simple
	 */
    public $simple;

	/**
	 * @link belongsToMany
	 * @var Post[]
	 */
    public $posts;
}