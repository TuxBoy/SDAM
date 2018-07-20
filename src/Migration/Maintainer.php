<?php
namespace TuxBoy;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use ICanBoogie\Inflector;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use TuxBoy\Annotation\Annotation;

/**
 * Class Maintainer
 * @package TuxBoy
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
    private $connection;

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
        $this->entity_path = Config::current()->params[Config::ENTITY_PATH] ?? 'App\Entity';
        try {
            $this->connection    = $this->connect();
            $this->schemaManager = $this->connection->getSchemaManager();
        }
        catch (DBALException $exception) {
            echo $exception->getMessage();
        }
    }

    /**
     * Détermine le nom de la table via le nom de la classe et la plurialise
     *
     * @param Schema $schema
     * @param string $className
     * @return Table (ex: Post => posts)
     * @throws SchemaException
     */
    public function getTableName(Schema $schema, string $className): Table
    {
        $tableName = Inflector::get()->pluralize(strtolower($className));
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
     * Le point de démarrage du maintener, c'est cette méthode qui déclanche toute le processus de migration
     *
     * @throws DBALException
     * @throws ReflectionException
     * @throws \PhpDocReader\AnnotationException
     */
    public function run(): void
    {
        $currentSchema = $this->schemaManager->createSchema();
        foreach ($this->entities as $entity) {
            $schema          = clone $currentSchema;
            $reflectionClass = new ReflectionClass($entity);
            $table           = $this->getTableName($schema, $reflectionClass->getShortName());
            $this->addPrimaryColumn($table);
            foreach ($reflectionClass->getProperties() as $property) {
                $propertyName = $property->getName();
                $typeField    = Annotation::of($entity, $propertyName)->getAnnotation('var')->getValue();
                if (
                    Annotation::of($entity, $propertyName)->hasAnnotation('link') &&
                    method_exists($this, Annotation::of($entity, $propertyName)->getAnnotation('link')->getValue())
                ) {
                    $relationMethod = Annotation::of($entity, $propertyName)->getAnnotation('link')->getValue();
                    $this->$relationMethod($schema, $table, $typeField);
                } else if (!$this->isClass($typeField)) {
                    $this->addNormalColumn($typeField, $entity, $table, $property);
                }
            }
            $migrationQueries = $currentSchema->getMigrateToSql($schema, $this->connection->getDatabasePlatform());
            foreach ($migrationQueries as $query) {
                try {
                    $this->connection->executeQuery($query);
                    file_put_contents('php://stdout', "The {$table->getName()} table has success created");
                }
                catch (SchemaException $exception) {
                    file_put_contents('php://stdout', $exception->getMessage());
                }
            }
        }
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
     * Rajoute des options, les options sont les arguments supplémentaires du champ (default, length...)
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
            if ($name !== 'var' && $name !== 'text') {
                $options[$name] = $value;
            }
            $entityInstance = new $entity;
            if (
                $property->getValue($entityInstance)
                || Annotation::of($entity, $propertyName)->hasAnnotation('default')
            ) {
                $defaultMethod = Annotation::of($entity, $propertyName)->getAnnotation('default')->getValue();
                if (method_exists($entity, $defaultMethod)) {
                    $value = $entityInstance->$defaultMethod();
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
        $params = Config::current()->params[Config::DATABASE] ?? ['url' => 'sqlite:///:memory:'];
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
        $fieldName  = $property->getName();
        $annotation = Annotation::of($entity, $property->getName());
        if ($typeField === Type::STRING && $annotation->hasAnnotation('text')) {
            $typeField = Type::TEXT;
        }
        $options = $this->addOptions($property, $entity, $annotation->getAnnotations());
        if (!$table->hasColumn($fieldName)) {
            $table->addColumn($property->getName(), $typeField, $options);
        } else {
            $table->changeColumn($property->getName(), $options);
        }
    }

    /**
     * Créé une clé étrangère dans la table en question pour une relation simple avec suppression
     * en cascade et met la champ en index.
     *
     * @param Schema $schema
     * @param Table $table
     * @param string $className
     *
     * @return string Retourne le nom du champ
     * @throws DatabaseException
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function belongsTo(Schema $schema, Table $table, string $className): string
    {
        $field = $this->classToForeignKey($className);
        if ($this->isForeignKey($field) && !$table->hasColumn($field)) {
            // Récupère la table sur la quelle la clé étrangère fait référence
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
     * @throws ReflectionException
     */
    public function classToForeignKey(string $className): string
    {
        return mb_strtolower($className) . '_id';
    }

}