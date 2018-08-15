<?php
namespace SDAM\Factory;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Exception;
use Faker\Generator;
use SDAM\Annotation\Annotation;
use SDAM\Annotation\AnnotationsName;
use SDAM\Tool;

/**
 * Class Factory
 *
 * Experimental class !!
 * @TODO Complete the system because it is very incomplete and does not handle all cases.
 */
class Factory
{

	/**
	 * @var string
	 */
	private $entity;

	/**
	 * @var FactoryInterface
	 */
	private $factoryClass;

	/**
	 * @var Connection
	 */
	private $connection;

	/**
	 * @var Tool
	 */
	private $tool;

	/**
	 * Factory constructor
	 *
	 * @param Connection $connection
	 * @param string $entity
	 * @param string $className
	 * @throws Exception
	 */
	public function __construct(Connection $connection, string $entity, string $className)
	{
		$this->entity       = $entity;
		$this->factoryClass = $this->getClassToClassName($className);
		$this->connection   = $connection;
		$this->tool         = new Tool;
	}

	/**
	 * @param Schema $schema
	 * @return int
	 * @throws \Doctrine\DBAL\DBALException
	 * @throws \Doctrine\DBAL\Schema\SchemaException
	 * @throws \PhpDocReader\AnnotationException
	 * @throws \ReflectionException
	 */
	public function generate(Schema $schema): int
	{
		$entityData = $this->factoryClass->define(\Faker\Factory::create());
		if (is_array($entityData)) {
			foreach ($entityData as $entity) {
				$this->insertFactoryData($entity, $schema);
			}
		} else {
			return $this->insertFactoryData($entityData, $schema);
		}
		return 1;
	}

	/**
	 * @param $entityData
	 * @param Schema $schema
	 * @return int
	 * @throws \Doctrine\DBAL\DBALException
	 * @throws \Doctrine\DBAL\Schema\SchemaException
	 * @throws \PhpDocReader\AnnotationException
	 * @throws \ReflectionException
	 */
	private function insertFactoryData($entityData, Schema $schema): int
	{
		$data       = [];
		$class      = new \ReflectionClass(get_class($entityData));
		array_map(function (\ReflectionProperty $property) use ($entityData, &$data) {
			$data[$property->getName()] = $property->getValue($entityData);
		}, $class->getProperties());
		foreach ($data as $key => $value) {
			if (
				Annotation::of($this->entity, $key)->hasAnnotation(AnnotationsName::P_STORE) ||
				Annotation::of($this->entity, $key)->hasAnnotation(AnnotationsName::P_LINK)
			) {
				unset($data[$key]);
			}
			if ($key === 'createdAt') {
				unset($data[$key]);
				$data['created_at'] = $value;
			}
			if ($key === 'updatedAt') {
				unset($data[$key]);
				$data['updated_at'] = $value;
			}
		}
		return $this->connection->insert($this->tool->getTableName($schema, get_class($entityData))->getName(), $data);
	}

	/**
	 * @param string $className
	 * @return FactoryInterface
	 * @throws Exception
	 */
	private function getClassToClassName(string $className): FactoryInterface
	{
		if (!class_exists($className)) {
			throw new Exception("The $className factory does not exist");
		}
		$factoryClass = new $className;
		if (!$factoryClass instanceof FactoryInterface) {
			throw new Exception("The factory class not implements FactoryInterface");
		}
		return $factoryClass;
	}

}