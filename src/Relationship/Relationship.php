<?php
namespace SDAM\Relationship;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
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
	 * @return string
	 */
	abstract public function getField(): string;

}