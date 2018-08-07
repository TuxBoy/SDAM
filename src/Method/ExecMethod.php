<?php
namespace SDAM\Method;

/**
 * Class ExecMethod
 */
class ExecMethod
{
	/**
	 * @var string
	 */
	private $method;

	/**
	 * ExecMethod constructor.
	 * @param string $method
	 * @throws MethodNotExist
	 */
	public function __construct(string $method)
	{
		$this->method = $this->invokeMethod($method);
	}

	/**
	 * @param string $method
	 * @return string
	 * @throws MethodNotExist
	 */
	private function invokeMethod(string $method): string
	{
		$methodToClass = 'SDAM\\Method\\' . ucfirst($method) . 'Method';
		if (!class_exists($methodToClass)) {
			throw new MethodNotExist();
		}
		$instanceMethod = new $methodToClass();
		if (!$instanceMethod instanceof MethodInterface) {
			throw new MethodNotExist();
		}
		return $instanceMethod;
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->method;
	}

}