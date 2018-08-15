<?php

namespace App\Factory;

use App\Entity\Question;
use SDAM\Factory\FactoryInterface;
use Faker\Generator as Faker;

class QuestionFactory implements FactoryInterface
{


	public function define(Faker $faker)
	{
		$questions = [];
		for ($i = 0; $i < 10; $i++) {
			$question = new Question;
			$question->name = $faker->name;
			$question->slug = $faker->slug;
			$question->content = $faker->text;
			$question->createdAt = date('Y-m-d H:i:s');
			$question->updatedAt = date('Y-m-d H:i:s');
			$questions[] = $question;
		}

		return $questions;
	}
}