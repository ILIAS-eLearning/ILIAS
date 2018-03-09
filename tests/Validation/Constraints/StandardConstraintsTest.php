<?php

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */
require_once("libs/composer/vendor/autoload.php");

use ILIAS\Validation;
use ILIAS\Data;

/**
 * Set of standard tests that should be checked for each additional constraint.
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class StandardConstraintsTest extends PHPUnit_Framework_TestCase {

	/**
	 * Add additional constraints to be added to be checked here
	 *
	 * @return array[[$constraint,$ok_values,$error_values]]
	 */
	public function constraintsProvider() {
		$f = new Validation\Factory(new Data\Factory());

		return array(array($f->isInt(), [2], [2.2]),
				array($f->greaterThan(5), [6], [4]),
				array($f->lessThan(5), [4], [6]),
				array($f->isNumeric(),
					//Values from http://php.net/manual/de/function.is-numeric.php
						[0,"1",1, 0x102, 0102, 0b101, 192e0,9.1],
						[null,"is numeric",[],[1]]),
				array($f->hasMinLength(10),
						["0123456789", "01234567890"],
						["", "012345678"]),
				array($f->hasMinLength(1),
						["0", "01234567890"],
						[""])
		);
	}

	/**
	 * @dataProvider constraintsProvider
	 */
	public function testAccept($constraint, $ok_values, $error_values) {
		foreach($ok_values as $ok_value){
			$this->assertTrue($constraint->accepts($ok_value));
		}
		foreach($error_values as $error_value){
			$this->assertFalse($constraint->accepts($error_value));
		}
	}

	/**
	 * @dataProvider constraintsProvider
	 */
	public function testCheck($constraint, $ok_values, $error_values) {
		$raised = false;

		try {
			foreach($ok_values as $ok_value){
				$constraint->check($ok_value);
			}
		} catch (UnexpectedValueException $e) {
			$raised = true;
		}


		$this->assertFalse($raised);

		try {
			foreach($error_values as $error_value){
				$constraint->check($error_value);
			}
		} catch (UnexpectedValueException $e) {
			$raised = true;
		}

		$this->assertTrue($raised);
	}

	/**
	 * @dataProvider constraintsProvider
	 */
	public function testProblemWith($constraint, $ok_values, $error_values) {
		foreach($ok_values as $ok_value){
			$this->assertNull($constraint->problemWith($ok_value));
		}
		foreach($error_values as $error_value){
			$this->assertInternalType("string", $constraint->problemWith($error_value));
		}
	}

	/**
	 * @dataProvider constraintsProvider
	 */
	public function testRestrict($constraint, $ok_values, $error_values) {
		$rf = new Data\Factory();

		foreach($ok_values as $ok_value){
			$ok = $rf->ok($ok_value);
			$result = $constraint->restrict($ok);
			$this->assertTrue($result->isOk());
		}


		foreach($error_values as $error_value){
			$ok = $rf->ok($error_value);
			$result = $constraint->restrict($ok);
			$this->assertTrue($result->isError());
		}

		$error = $rf->error("text");
		$result = $constraint->restrict($error);
		$this->assertSame($error, $result);
	}

	/**
	 * @dataProvider constraintsProvider
	 */
	public function testWithProblemBuilder($constraint, $ok_values, $error_values) {
		$new_constraint = $constraint->withProblemBuilder(function() { return "This was a vault"; });
		$this->assertEquals("This was a vault", $new_constraint->problemWith($error_values[0]));
	}
}
