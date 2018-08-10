<?php
namespace SDAM\Relationship;

class BelongsTo extends Relationship
{

	/**
	 * @return string|null
	 * @throws \Doctrine\DBAL\Schema\SchemaException
	 * @throws \PhpDocReader\AnnotationException
	 * @throws \ReflectionException
	 */
	public function create(): ?string
	{
		$field = $this->classToForeignKey($this->shortClassName);
		if ($this->tool->isForeignKey($field) && !$this->table->hasColumn($field)) {
			// The table to which the foreign key refers
			$foreignTable = $this->tool->getTableName($this->schema, $this->className);
			$this->tool->addPrimaryColumn($foreignTable);
			$this->createForeignKey($this->tool->getTableName($this->schema, $this->className), $field);
		}
		return $field;
	}
}