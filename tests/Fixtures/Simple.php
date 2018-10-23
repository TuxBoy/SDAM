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

	/**
	 * @var string
	 */
	private $private;

	/**
	 * @null
	 * @var string
	 */
	public $field_null;

	/**
	 * @return string|null
	 */
	public function getPrivate(): ?string
	{
		return $this->private;
	}
}
