<?php

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Validation;
use ILIAS\Data;

/**
 * TestCase for the factory of constraints
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ParallelTest extends PHPUnit_Framework_TestCase {
	protected function setUp() {
		$this->f = new Validation\Factory();
	}

	protected function tearDown() {
		$this->f = null;
	}

	public function testAccept() {
		$constraint = $this->f->parallel(array($this->f->isInt(), $this->f->greaterThan(3), $this->f->lessThan(5)));

		$this->assertTrue($constraint->accepts(4));
		$this->assertFalse($constraint->accepts(2));
	}

	public function testCheck() {
		$constraint = $this->f->sequential(array($this->f->isInt(), $this->f->greaterThan(3), $this->f->lessThan(5)));
		$raised = false;

		try {
			$constraint->check(4);
		} catch (UnexpectedValueException $e) {
			$raised = true;
		}

		$this->assertFalse($raised);

		$constraint = $this->f->parallel(array($this->f->isInt(), $this->f->greaterThan(3), $this->f->lessThan(1)));
		try {
			$constraint->check(2);
			$raised = false;
		} catch (UnexpectedValueException $e) {
			$this->assertEquals("The checked value is not greater.The checked value is greater than.", $e->getMessage());
			$raised = true;
		}

		$this->assertTrue($raised);
	}

	public function testProblemWith() {
		$constraint = $this->f->parallel(array($this->f->isInt(), $this->f->greaterThan(3), $this->f->lessThan(5)));

		$this->assertNull($constraint->problemWith(4));

		$constraint = $this->f->parallel(array($this->f->isInt(), $this->f->greaterThan(3), $this->f->lessThan(1)));
		$this->assertInternalType("string", $constraint->problemWith(2));
		$this->assertEquals("The checked value is not greater.The checked value is greater than.", $constraint->problemWith(2));
	}

	public function testRestrict() {
		$constraint = $this->f->parallel(array($this->f->isInt(), $this->f->greaterThan(3), $this->f->lessThan(5)));

		$rf = new Data\Factory();
		$ok = $rf->ok(4);
		$ok2 = $rf->ok(2);
		$error = $rf->error("text");

		$result = $constraint->restrict($ok);
		$this->assertTrue($result->isOk());

		$result = $constraint->restrict($ok2);
		$this->assertTrue($result->isError());

		$result = $constraint->restrict($error);
		$this->assertSame($error, $result);
	}

	public function testWithProblemBuilder() {
		$constraint = $this->f->parallel(array($this->f->isInt(), $this->f->greaterThan(3), $this->f->lessThan(1)));

		$new_constraint = $constraint->withProblemBuilder(function() { return "This was a vault"; });
		$this->assertEquals("This was a vault", $new_constraint->problemWith(2));
	}
}