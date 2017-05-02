<?php

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Validation;
use ILIAS\Data;

/**
 * TestCase for the factory of constraints
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class NotTest extends PHPUnit_Framework_TestCase {
	/**
	 * @dataprovider constraintsProvider
	 */
	public function testAccept($constraint, $ok_value, $error_value) {
		$this->assertFalse($constraint->accepts($ok_value));
		$this->assertTrue($constraint->accepts($error_value));
	}

	/**
	 * @dataprovider constraintsProvider
	 */
	public function testCheck($constraint, $ok_value, $error_value) {
		$raised = false;

		try {
			$constraint->check($error_value);
		} catch (UnexpectedValueException $e) {
			$raised = true;
		}

		$this->assertTrue($raised);

		try {
			$constraint->check($ok_value);
			$raised = false;
		} catch (UnexpectedValueException $e) {
			$raised = true;
		}

		$this->assertFalse($raised);
	}

	/**
	 * @dataprovider constraintsProvider
	 */
	public function testProblemWith($constraint, $ok_value, $error_value) {
		$this->assertNull($constraint->problemWith($error_value));
		$this->assertInternalType("string", $constraint->problemWith($ok_value));
	}

	/**
	 * @dataprovider constraintsProvider
	 */
	public function testRestrict($constraint, $ok_value, $error_value) {
		$rf = new Data\Factory();
		$ok = $rf->ok($error_value);
		$ok2 = $rf->ok($ok_value);
		$error = $rf->error("text");

		$result = $constraint->restrict($ok);
		$this->assertTrue($result->isOk());

		$result = $constraint->restrict($ok2);
		$this->assertTrue($result->isError());

		$result = $constraint->restrict($error);
		$this->assertSame($error, $result);
	}

	/**
	 * @dataprovider constraintsProvider
	 */
	public function testWithProblemBuilder($constraint, $ok_value, $error_value) {
		$new_constraint = $this->is_int->withProblemBuilder(function($value) { return "This was a vault"; });
		$this->asserEquals("This was a fault", $new_constraint->problemWith($ok_value));
	}

	public function constraintsProvider() {
		$this->f = new Validation\Factory();

		return array(array($this->f->isInt(), 2, 2.2),
					 array($this->f->greaterThan(5), 6, 4)
					 array($this->f->lessThan(5), 4, 6)
			);
	}
}