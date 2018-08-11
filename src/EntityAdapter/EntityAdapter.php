<?php
namespace SDAM\EntityAdapter;

use SDAM\Config;

/**
 * Class EntityAdapter
 *
 * @package SDAM
 */
class EntityAdapter implements EntityAdapterInterface
{
	/**
	 * @var string
	 */
	private $path;

	/**
	 * @var string[]
	 */
	private $ignored;

	/**
	 * EntityAdapter constructor.
	 * @param string $path
	 * @param $ignored string[]|string the list of entities to ignore
	 */
	public function __construct(string $path, $ignored = [])
	{
		$this->path    = rtrim($path, '/'); // Delete last / if exist
		$this->ignored = is_string($ignored) ? [$ignored] : $ignored;
	}

	/**
	 * @return string[] All entities found in the specified path
	 */
	public function toArray(): array
	{
		$items    = glob($this->path . '/*.php');
		$items    = str_replace('.php', '', $items);
		$entities = array_filter(array_map(function ($item) {
			$partials = explode('/', $item);
			return Config::current()->getParams()[Config::ENTITY_PATH] . '\\' . end($partials);
		}, $items), function ($entity) { return !in_array($entity, $this->ignored); });
		return $entities;
	}

	/**
	 * Count elements of an object
	 * @return int
	 */
	public function count(): int
	{
		return count($this->toArray());
	}
}