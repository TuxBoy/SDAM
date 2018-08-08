<?php
namespace SDAM\Relationship;

class BelongsTo extends Relationship
{

	/**
	 * @return string
	 * @throws \Doctrine\DBAL\Schema\SchemaException
	 * @throws \PhpDocReader\AnnotationException
	 * @throws \ReflectionException
	 */
	public function getField(): string
	{
		$field = $this->classToForeignKey($this->shortClassName);
		if ($this->tool->isForeignKey($field) && !$this->table->hasColumn($field)) {
			// The table to which the foreign key refers
			$foreignTable = $this->tool->getTableName($this->schema, $this->className);
			$this->tool->addPrimaryColumn($foreignTable);
			$options['unsigned'] = true;
			$options['notnull']  = false;
			$this->table->addColumn($field, 'integer', $options);
			if (!$this->table->hasIndex($field . '_index')) {
				$this->table->addIndex([$field], $field . '_index');
			}
			$this->table->addForeignKeyConstraint(
				$this->tool->getTableName($this->schema, $this->className),
				[$field],
				['id'],
				['onDelete' => 'CASCADE'],
				$field . '_contrain'
			);
		}
		return $field;
	}
}