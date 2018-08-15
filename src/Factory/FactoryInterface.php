<?php
namespace SDAM\Factory;

use Faker\Generator as Faker;

/**
 * Interface FactoryInterface
 * @package SDAM\Factory
 */
interface FactoryInterface
{

	/**
	 * @param Faker $faker
	 * @return object|object[]
	 */
	public function define(Faker $faker);

}