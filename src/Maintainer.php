<?php
namespace SDAM;

use DateTime;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use ICanBoogie\Inflector;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use SDAM\Annotation\Annotation;
use SDAM\Annotation\AnnotationsName;
use SDAM\EntityAdapter\EntityAdapterInterface;
use SDAM\Factory\Factory;
use SDAM\Method\ExecMethod;
use SDAM\Method\Methods;
use SDAM\Relationship\BelongsTo;
use SDAM\Relationship\BelongsToMany;

/**
 * Class Maintainer
 *
 * @package SDAM
 */
class Maintainer
{

    /**
     * @var string[]
     */
    public $entities;

    /**
     * @var Connection
     */
    public $connection;

    /**
     * @var AbstractSchemaManager
     */
    private $schemaManager;

    /**
     * The entities namespace
     *
     * @var string
     */
    private $entity_path;

	/**
	 * @var Tool
	 */
	private $tool;

	/**
	 * Maintainer constructor
	 *
	 * @param string[] $entities
	 * @param EntityAdapterInterface $entityAdapter
	 */
    public function __construct(array $entities = [], ?EntityAdapterInterface $entityAdapter = null)
    {
        $this->entities    = empty($entities) ? $entityAdapter->toArray() : $entities;
        $this->entity_path = Config::current()->getParams()[Config::ENTITY_PATH] ?? 'App\Entity';
        $this->tool        = new Tool();
        try {
            $this->connection    = $this->connect();
            $this->schemaManager = $this->connection->getSchemaManager();
        }
        catch (DBALException $exception) {
            die($exception->getMessage());
        }
    }

    /**
     * The start point of the maintainer is this method that triggers the entire migration process
     *
     * @throws DBALException
     * @throws ReflectionException
     * @throws \PhpDocReader\AnnotationException
     * @throws \Throwable
     */
    public function run()
    {
        $currentSchema = $this->schemaManager->createSchema();
        foreach ($this->entities as $entity) {
            $schema          = clone $currentSchema;
            $reflectionClass = new ReflectionClass($entity);
            $table           = $this->tool->getTableName($schema, $entity);
            $properties      = $reflectionClass->getProperties();
            $this->tool->addPrimaryColumn($table);
            foreach ($properties as $property) {
            	if ($property->isPrivate()) {
            		$property->setAccessible(true);
				}
                $propertyName = $property->getName();
                $typeField    = Annotation::of($entity, $propertyName)
					->getAnnotation(AnnotationsName::P_VAR)
					->getValue();
                $typeField    = str_replace('[]', '', $typeField);
				$isStored     = $this->isStoredProperty($entity, $propertyName);
				if (($typeField === DateTime::class || $typeField === '\DateTime') && $isStored) {
					$this->addNormalColumn('datetime', $entity, $table, $property);
				}
				if ($this->annotationIs($entity, $propertyName, 'belongsTo') && $isStored) {
					$annotation    = Annotation::of($entity, $propertyName);
					$fullClassName = $annotation->getObjectVar();
					(new BelongsTo($schema, $table, $fullClassName, $typeField))->create();
                } elseif ($this->annotationIs($entity, $propertyName, 'belongsToMany') && $isStored) {
					$fullClassName = Config::current()->getParams()[Config::ENTITY_PATH] . '\\' . $typeField;
					$belongsToMany = new BelongsToMany($schema, $table, $fullClassName, $typeField);
					$pivotTable    = $belongsToMany->createPivotTable();
					$belongsToMany->setTable($pivotTable)->create();
				} else if (!$this->isClass($typeField) && $isStored && !$this->tool->isForeignKey($propertyName)) {
                    $this->addNormalColumn($typeField, $entity, $table, $property);
                }

                if (Annotation::of($entity)->hasAnnotation(AnnotationsName::C_FACTORY)) {
					$factoryValue = Annotation::of($entity)->getAnnotation(AnnotationsName::C_FACTORY)->getValue();
					return (new Factory($this->connection, $entity, $factoryValue))->generate($schema);
				}
            }
            if (
                !isset(Config::current()->getParams()[Config::AUTO_DROP_FIELD])
                || (Config::current()->getParams()[Config::AUTO_DROP_FIELD] !== false)
            ) {
                $this->dropColumn($properties, $table);
            }
            $migrationQueries = $currentSchema->getMigrateToSql($schema, $this->connection->getDatabasePlatform());
            $this->connection->transactional(function () use ($migrationQueries, $table) {
                foreach ($migrationQueries as $query) {
                    try {
                        $this->connection->executeQuery($query);
                        //file_put_contents('php://stdout', "The {$table->getName()} table has success created");
                    }
                    catch (SchemaException $exception) {
                        //file_put_contents('php://stdout', $exception->getMessage());
                    }
                }
            });
        }
        return null;
    }

	/**
	 * @param string $entity
	 * @param string $propertyName
	 * @param string $value
	 * @return bool
	 * @throws ReflectionException
	 * @throws \PhpDocReader\AnnotationException
	 */
    private function annotationIs($entity, string $propertyName, string $value): bool
	{
		return Annotation::of($entity, $propertyName)->hasAnnotation(AnnotationsName::P_LINK) &&
		Annotation::of($entity, $propertyName)->getAnnotation(AnnotationsName::P_LINK)->getValue() === $value;
	}

    /**
     * @param string $entity
     * @param string $propertyName
     * @return bool true if the property may be persistent in database11
     * @throws ReflectionException
     * @throws \PhpDocReader\AnnotationException
     */
    private function isStoredProperty(string $entity, string $propertyName): bool
    {
        return !boolval(Annotation::of($entity, $propertyName)
			->getAnnotation(AnnotationsName::P_STORE)->getValue());
    }

    /**
     * @param string $field
     * @return boolean
     */
    public function isClass(string $field): bool
    {
        return class_exists($field);
    }

	/**
	 * Add options, the options are the additional arguments of the field (default, length...)
	 *
	 * @param ReflectionProperty $property
	 * @param string $entity
	 * @param array $annotations
	 * @return string[]
	 * @throws ReflectionException
	 * @throws \PhpDocReader\AnnotationException
	 * @throws Method\MethodNotExist
	 */
    private function addOptions(ReflectionProperty $property, string $entity, array $annotations): array
    {
        $options           = [];
        $propertyName      = $property->getName();
        $options['length'] = 255;
        foreach ($annotations as $name => $value) {
            if ($name !== AnnotationsName::P_VAR && $name !== AnnotationsName::P_TEXT) {
                $options[$name] = $value;
            }
            $entityInstance = new $entity;
            if (
                $property->getValue($entityInstance)
                || Annotation::of($entity, $propertyName)->hasAnnotation(AnnotationsName::P_DEFAULT)
            ) {
                $defaultMethod = Annotation::of($entity, $propertyName)->getAnnotation(AnnotationsName::P_DEFAULT)->getValue();
                if (method_exists($entity, $defaultMethod)) {
                    $value = $entityInstance->$defaultMethod();
                } else if (!method_exists($entity, $defaultMethod) && Methods::isMethod($defaultMethod)) {
					$value = new ExecMethod($defaultMethod);
				} else {
                    $value = $property->getValue($entityInstance);
                }
                $options['default'] = $value;
            }
        }
        return $options;
    }

    /**
     * @return Connection
     * @throws DBALException
     */
    private function connect(): Connection
    {
        $config = new Configuration();
        $params = Config::current()->getParams()[Config::DATABASE] ?? [];
        return DriverManager::getConnection($params, $config);
    }

	/**
	 * @param string $typeField
	 * @param string $entity
	 * @param Table $table
	 * @param ReflectionProperty $property
	 * @throws ReflectionException
	 * @throws \PhpDocReader\AnnotationException
	 * @throws Method\MethodNotExist
	 */
    private function addNormalColumn(string $typeField, string $entity, Table $table, ReflectionProperty $property): void
    {
        $fieldName  = $this->getFieldName($property);
        $annotation = Annotation::of($entity, $property->getName());
        if ($typeField === Type::STRING && $annotation->hasAnnotation(AnnotationsName::P_TEXT)) {
            $typeField = Type::TEXT;
        }
        $options = $this->addOptions($property, $entity, $annotation->getAnnotations());
        if (!$table->hasColumn($fieldName)) {
            $table->addColumn($fieldName, $typeField, $options);
        } else {
            $table->changeColumn($fieldName, $options);
        }
    }

    /**
     * Transform property name to field name (e. createdAt => created_at)
     *
     * @param ReflectionProperty $property
     * @return string
     */
    private function getFieldName(ReflectionProperty $property): string
    {
        return Inflector::get()->underscore($property->getName());
    }

    /**
     * @param ReflectionProperty[] $properties
     * @param Table $table
     * @return Table
     */
    private function dropColumn(array $properties, Table $table)
    {
        $columns = $table->getColumns();
        // Unset primary key of array
        if (isset($columns['id'])) {
            unset($columns['id']);
        }
        // Unset foreign key => field_id
        $columns = array_filter($columns, function (Column $column) {
            return !$this->tool->isForeignKey($column->getName());
        });
        $arrayProperties = [];
        array_map(function (ReflectionProperty $property) use (&$arrayProperties) {
            $propertyName = $this->getFieldName($property);
            $arrayProperties[$propertyName] = $propertyName;
        }, $properties);
        $arrayColumns = array_map(function (Column $column) {
            return $column->getName();
        }, $columns);
        $dropColumns = array_diff($arrayColumns, $arrayProperties);
        foreach ($dropColumns as $dropColumn) {
            $table->dropColumn($dropColumn);
        }
        return $table;
    }

}