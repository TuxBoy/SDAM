<?php
namespace SDAM\Method;

/**
 * Class Methods
 * @package SDAM\Method
 */
abstract class Methods
{

	const NOW = 'now';

	/**
	 * @param string $method
	 * @return bool
	 * @throws \ReflectionException
	 */
	public static function isMethod(?string $method = null): bool
	{
		$class = new \ReflectionClass(static::class);
		return in_array($method, $class->getConstants());
	}

}