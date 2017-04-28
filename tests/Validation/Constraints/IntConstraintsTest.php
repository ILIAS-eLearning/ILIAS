<?php

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Validation;
use ILIAS\Data;

/**
 * TestCase for the factory of constraints
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class IntConstraintsTest extends PHPUnit_Framework_TestCase {
	/**
	 * @dataprovider constraintsProvider
	 */
	public function testAccept($constraint, $ok_value, $error_value) {
		$this->assertTrue($this->constraint->accepts($ok_value));
		$this->assertFalse($this->constraint->accepts($error_value));
	}

	/**
	 * @dataprovider constraintsProvider
	 */
	public function testCheck($constraint, $ok_value, $error_value) {
		$raised = false;
		try {
			$this->constraint->check($ok_value);
		} catch (UnexpectedValueException $e) {
			$raised = true;
		}

		$this->assertFalse($raised);

		try {
			$this->constraint->check($error_value);
		} catch (UnexpectedValueException $e) {
			$raised = true;
		}

		$this->assertTrue($raised);
	}

	/**
	 * @dataprovider constraintsProvider
	 */
	public function testProblemWith($constraint, $ok_value, $error_value) {
		$this->asserNull($this->constraint->problemWith($ok_value));
		$this->assertInternalType("string", $this->constraint->problemWith($error_value));
	}

	/**
	 * @dataprovider constraintsProvider
	 */
	public function testRestrict($constraint, $ok_value, $error_value) {
		$rf = new Data\Factory();
		$ok = $rf->ok($ok_value);
		$ok2 = $rf->ok($error_value);
		$error = $rf->error("text");

		$result = $this->constraint->restrict($ok);
		$this->assertTrue($result->isOk());

		$result = $this->constraint->restrict($ok2);
		$this->assertTrue($result->isError());

		$result = $this->constraint->restrict($error);
		$this->assertSame($error, $result);
	}

	/**
	 * @dataprovider constraintsProvider
	 */
	public function testWithProblemBuilder($constraint, $ok_value, $error_value) {
		$new_constraint = $this->is_int->withProblemBuilder(function($value) { return "This was a vault"; });
		$this->asserEquals("This was a fault", $new_constraint->problemWith($error_value));
	}

	public function constraintsProvider() {
		$this->f = new Validation\Factory();

		return array(array($this->f->isInt(), 2, 2.2),
					 array($this->f->greaterThan(5), 6, 4)
					 array($this->f->lessThan(5), 4, 6)
			);
	}
}