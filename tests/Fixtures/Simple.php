<?php
namespace UnitTest\Fixtures;
use SDAM\Traits\HasCreatedAt;

/**
 * Class FakeEntity
 */
class Simple
{
	use HasCreatedAt;

    /**
     * @var string
     */
    public $name;
}