<?php
namespace SDAM;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use ICanBoogie\Inflector;
use ReflectionClass;
use SDAM\Annotation\Annotation;
use SDAM\Annotation\AnnotationsName;

/**
 * Class Tool
 * @package SDAM
 */
class Tool
{

	/**
	 * @param Table $table
	 * @param string $primaryName
	 */
	public function addPrimaryColumn(Table $table, string $primaryName = 'id'): void
	{
		if (!$table->hasPrimaryKey()) {
			$table->addColumn($primaryName, 'integer', ['unsigned' => true, 'autoincrement' => true]);
			$table->setPrimaryKey([$primaryName]);
		}
	}

	/**
	 * Determine the name of the table via the class name and plurialize, or storeName annotation
	 *
	 * @param Schema $schema
	 * @param string $className
	 * @return Table (ex: Post => posts)
	 * @throws \Doctrine\DBAL\Schema\SchemaException
	 * @throws \PhpDocReader\AnnotationException
	 * @throws \ReflectionException
	 */
	public function getTableName(Schema $schema, string $className): Table
	{
		if (Annotation::of($className)->hasAnnotation(AnnotationsName::C_STORE_NAME)) {
			$tableName = Annotation::of($className)
				->getAnnotation(AnnotationsName::C_STORE_NAME)
				->getValue();
		} else {
			$class     = new ReflectionClass($className);
			$tableName = Inflector::get()->pluralize(strtolower($class->getShortName()));
		}
		return $schema->hasTable($tableName) ? $schema->getTable($tableName) : $schema->createTable($tableName);
	}

	/**
	 * True if the field is a foreign key (e. field_id).
	 *
	 * @param string $field
	 *
	 * @return bool
	 */
	public function isForeignKey(string $field): bool
	{
		return (bool) (mb_substr($field, -3) === '_id');
	}

}