<?php
namespace SDAM;

use DateTime;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use ICanBoogie\Inflector;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use SDAM\Annotation\Annotation;
use SDAM\Annotation\AnnotationsName;
use SDAM\Method\ExecMethod;
use SDAM\Method\Methods;

/**
 * Class Maintainer
 *
 * @package SDAM
 */
class Maintainer
{

    /**
     * Toutes les entitées renseignées pour faire la migration
     *
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
     * Maintainer constructor
     *
     * @param string[] $entities List des entitées a charger pour la migration
     */
    public function __construct(array $entities = [])
    {
        $this->entities    = $entities;
        $this->entity_path = Config::current()->getParams()[Config::ENTITY_PATH] ?? 'App\Entity';
        try {
            $this->connection    = $this->connect();
            $this->schemaManager = $this->connection->getSchemaManager();
        }
        catch (DBALException $exception) {
            echo $exception->getMessage();
        }
    }

    /**
     * Determine the name of the table via the class name and plurialize, or storeName annotation
     *
     * @param Schema $schema
     * @param string $className
     * @return Table (ex: Post => posts)
     * @throws ReflectionException
     * @throws SchemaException
     * @throws \PhpDocReader\AnnotationException
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
     * @param Table $table
     * @param string $primaryName
     */
    private function addPrimaryColumn(Table $table, string $primaryName = 'id'): void
    {
        if (!$table->hasPrimaryKey()) {
            $table->addColumn($primaryName, 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $table->setPrimaryKey([$primaryName]);
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
    public function run(): void
    {
        $currentSchema = $this->schemaManager->createSchema();
        foreach ($this->entities as $entity) {
            $schema          = clone $currentSchema;
            $reflectionClass = new ReflectionClass($entity);
            $table           = $this->getTableName($schema, $entity);
            $properties      = $reflectionClass->getProperties();
            $this->addPrimaryColumn($table);
            foreach ($properties as $property) {
                $propertyName = $property->getName();
                $typeField    = Annotation::of($entity, $propertyName)->getAnnotation(AnnotationsName::P_VAR)->getValue();
                $isStored     = $this->isStoredProperty($entity, $propertyName);
                if ($typeField === DateTime::class || $typeField === '\DateTime' && $isStored) {
                    $this->addNormalColumn('datetime', $entity, $table, $property);
                }
                if (
                    Annotation::of($entity, $propertyName)->hasAnnotation(AnnotationsName::P_LINK) &&
                    method_exists($this, Annotation::of($entity, $propertyName)->getAnnotation(AnnotationsName::P_LINK)->getValue()) &&
                    $isStored
                ) {
                    $annotation     = Annotation::of($entity, $propertyName);
                    $relationMethod = $annotation->getAnnotation(AnnotationsName::P_LINK)->getValue();
                    $fullClassName  = $annotation->getObjectVar();
                    $this->$relationMethod($schema, $table, $fullClassName, $typeField);
                } else if (!$this->isClass($typeField) && $isStored && !$this->isForeignKey($propertyName)) {
                    $this->addNormalColumn($typeField, $entity, $table, $property);
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
                        file_put_contents('php://stdout', "The {$table->getName()} table has success created");
                    }
                    catch (SchemaException $exception) {
                        file_put_contents('php://stdout', $exception->getMessage());
                    }
                }
            });
        }
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
        return !boolval(Annotation::of($entity, $propertyName)->getAnnotation(AnnotationsName::P_STORE)->getValue());
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
        $params = Config::current()->getParams()[Config::DATABASE] ?? ['url' => 'sqlite:///:memory:'];
        return DriverManager::getConnection($params, $config);
    }

    /**
     * @param string $typeField
     * @param string $entity
     * @param Table $table
     * @param ReflectionProperty $property
     * @throws ReflectionException
     * @throws \PhpDocReader\AnnotationException
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
     * Create a foreign key in the table in question for a simple relationship
     * with cascading deletion and put the field in index.
     *
     * @param Schema $schema
     * @param Table $table
     * @param string $className
     *
     * @param string $shortClassName
     * @return string Retourne le nom du champ
     * @throws ReflectionException
     * @throws SchemaException
     * @throws \PhpDocReader\AnnotationException
     */
    public function belongsTo(Schema $schema, Table $table, string $className, string $shortClassName): string
    {
        $field = $this->classToForeignKey($shortClassName);
        if ($this->isForeignKey($field) && !$table->hasColumn($field)) {
            // The table to which the foreign key refers
            $foreignTable = $this->getTableName($schema, $className);
            $this->addPrimaryColumn($foreignTable);
            $options['unsigned'] = true;
            $options['notnull']  = false;
            $table->addColumn($field, 'integer', $options);
            if (!$table->hasIndex($field . '_index')) {
                $table->addIndex([$field], $field . '_index');
            }
            $table->addForeignKeyConstraint(
                $this->getTableName($schema, $className),
                [$field],
                ['id'],
                ['onDelete' => 'CASCADE'],
                $field . '_contrain'
            );
        }
        return $field;
    }

    /**
     * True if the field is a foreign key (e. field_id).
     *
     * @param string $field
     *
     * @return bool
     */
    private function isForeignKey(string $field): bool
    {
        return (bool) (mb_substr($field, -3) === '_id');
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
            return !$this->isForeignKey($column->getName());
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