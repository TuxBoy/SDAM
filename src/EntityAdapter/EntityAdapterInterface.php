<?php
namespace SDAM\EntityAdapter;

use Countable;

interface EntityAdapterInterface extends Countable
{

	/**
	 * @return string[] All entities found in the specified path
	 */
	public function toArray(): array;

}