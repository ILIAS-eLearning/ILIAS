<?php

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */
require_once("libs/composer/vendor/autoload.php");

use ILIAS\Validation;
use ILIAS\Data;

/**
 * TestCase for the factory of constraints
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ValidationConstraintsCustomTest extends PHPUnit_Framework_TestCase {
	/**
	 * @dataProvider constraintsProvider
	 */
	public function testAccept($constraint, $ok_value, $error_value, $message) {
		$this->assertTrue($constraint->accepts($ok_value));
		$this->assertFalse($constraint->accepts($error_value));
	}

	/**
	 * @dataProvider constraintsProvider
	 */
	public function testCheck($constraint, $ok_value, $error_value, $message) {
		$raised = false;
		try {
			$constraint->check($ok_value);
		} catch (UnexpectedValueException $e) {
			$raised = true;
		}

		$this->assertFalse($raised);

		try {
			$constraint->check($error_value);
		} catch (UnexpectedValueException $e) {
			$this->assertEquals($message, $e->getMessage());
			$raised = true;
		}

		$this->assertTrue($raised);
	}

	/**
	 * @dataProvider constraintsProvider
	 */
	public function testProblemWith($constraint, $ok_value, $error_value, $message) {
		$this->assertNull($constraint->problemWith($ok_value));
		$this->assertInternalType("string", $constraint->problemWith($error_value));
		$this->assertEquals($message, $constraint->problemWith($error_value));
	}

	/**
	 * @dataProvider constraintsProvider
	 */
	public function testRestrict($constraint, $ok_value, $error_value, $message) {
		$rf = new Data\Factory();
		$ok = $rf->ok($ok_value);
		$ok2 = $rf->ok($error_value);
		$error = $rf->error("text");

		$result = $constraint->restrict($ok);
		$this->assertTrue($result->isOk());

		$result = $constraint->restrict($ok2);
		$this->assertTrue($result->isError());

		$result = $constraint->restrict($error);
		$this->assertSame($error, $result);
	}

	/**
	 * @dataProvider constraintsProvider
	 */
	public function testWithProblemBuilder($constraint, $ok_value, $error_value, $message) {
		$new_constraint = $constraint->withProblemBuilder(function() { return "This was a vault"; });
		$this->assertEquals("This was a vault", $new_constraint->problemWith($error_value));
	}

	public function constraintsProvider() {
		$f = new Validation\Factory(new Data\Factory());

		return array(array($f->custom(function($value) { return $value > 1;}, "Value is not greater"), 2, 1, "Value is not greater"),
					 array($f->custom(function($value) { return $value < 2;}, "Value is not greater"), 1, 2, "Value is not greater")
			);
	}
}
