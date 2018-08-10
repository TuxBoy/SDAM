<?php
namespace SDAM\Relationship;

use Doctrine\DBAL\Schema\Table;
use ICanBoogie\Inflector;

/**
 * Relationship BelongsToMany
 *
 * @package SDAM\Relationship
 */
class BelongsToMany extends Relationship
{

	/**
	 * @var Table
	 */
	private $originTable;

	/**
	 * @return string|null
	 * @throws \Doctrine\DBAL\Schema\SchemaException
	 * @throws \PhpDocReader\AnnotationException
	 * @throws \ReflectionException
	 */
	public function create(): ?string
	{
		$pivotTable = $this->table;
		$this->tool->addPrimaryColumn($pivotTable);
		$relationTable = $this->tool->getTableName($this->schema, $this->className);
		$this->tool->addPrimaryColumn($relationTable);
		$this->createForeignKey($relationTable, $this->classToForeignKey($this->shortClassName));
		$this->createForeignKey($this->originTable, $this->classToForeignKey(Inflector::get()->singularize($this->originTable->getName())));

		return null;
	}

	/**
	 * @return Table
	 * @throws \Doctrine\DBAL\Schema\SchemaException
	 */
	public function createPivotTable(): Table
	{
		$this->originTable = $this->table;
		$pivotTableName    = $this->getPivotTable($this->table->getName(), $this->shortClassName);
		$pivotTable        = $this->schema->hasTable($pivotTableName)
			? $this->schema->getTable($pivotTableName)
			: $this->schema->createTable($pivotTableName);
		return $pivotTable;
	}

	/**
	 * @param string $primaryTable
	 * @param string $relationTable
	 * @return string
	 */
	private function getPivotTable(string $primaryTable, string $relationTable): string
	{
		return $primaryTable . '_' . strtolower(Inflector::get()->pluralize($relationTable));
	}
}