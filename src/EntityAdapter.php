<?php
/**
 * Created by PhpStorm.
 * User: tuxboy
 * Date: 11/08/18
 * Time: 14:49
 */

namespace SDAM;


use Countable;

class EntityAdapter implements Countable
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
	 * @link http://php.net/manual/en/countable.count.php
	 * @return int The custom count as an integer.
	 * </p>
	 * <p>
	 * The return value is cast to an integer.
	 * @since 5.1.0
	 */
	public function count(): int
	{
		return count($this->toArray());
	}
}