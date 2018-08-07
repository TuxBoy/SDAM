<?php
namespace SDAM\Method;

use DateTime;

/**
 * NowMethod
 */
class NowMethod implements MethodInterface
{

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return (new DateTime())->format('Y-m-d H:i:s');
	}
}