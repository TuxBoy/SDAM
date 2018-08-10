<?php
namespace SDAM\Relationship;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use SDAM\Tool;

/**
 * Class Relationship
 * @package SDAM\Relationship
 */
abstract class Relationship
{

	/**
	 * @var Schema
	 */
	protected $schema;

	/**
	 * @var Table
	 */
	protected $table;

	/**
	 * @var string
	 */
	protected $className;

	/**
	 * @var string
	 */
	protected $shortClassName;

	/**
	 * @var Tool
	 */
	protected $tool;

	public function __construct(Schema $schema, Table $table, string $className, string $shortClassName)
	{
		$this->schema         = $schema;
		$this->table          = $table;
		$this->className      = $className;
		$this->shortClassName = $shortClassName;
		$this->tool           = new Tool();
	}

	/**
	 * @param string $className
	 * @return string l'équivalent du nom de la classe passé en paramètre en clé étrangère (Category => category_id)
	 */
	public function classToForeignKey(string $className): string
	{
		return mb_strtolower($className) . '_id';
	}

	/**
	 * @param Table|string $foreignTable
	 * @param string $foreignField
	 */
	protected function createForeignKey($foreignTable, string $foreignField)
	{
		$options['unsigned'] = true;
		$options['notnull']  = false;
		if (!$this->table->hasColumn($foreignField)) {
			$this->table->addColumn($foreignField, Type::INTEGER, $options);
			if (!$this->table->hasIndex($foreignField . '_index')) {
				$this->table->addIndex([$foreignField], $foreignField . '_index');
			}
			$this->table->addForeignKeyConstraint(
				$foreignTable,
				[$foreignField],
				['id'],
				['onDelete' => 'CASCADE'],
				$foreignField . '_contrain'
			);
		}
	}

	/**
	 * @param Table|null $table
	 * @return Relationship
	 */
	public function setTable(?Table $table = null): self
	{
		if ($table) {
			$this->table = $table;
		}
		return $this;
	}

	/**
	 * @return string|null
	 */
	abstract public function create(): ?string;

}