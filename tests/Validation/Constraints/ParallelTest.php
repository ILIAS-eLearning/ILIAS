<?php

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */
require_once("libs/composer/vendor/autoload.php");

use ILIAS\Validation;
use ILIAS\Data;

/**
 * TestCase for the parellel constraint
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ParallelTest extends PHPUnit_Framework_TestCase {
	protected function setUp() {
		$this->f = new Validation\Factory(new Data\Factory());
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
			$this->assertEquals("'2' is not greater than '3'.'2' is greater than '1'.", $e->getMessage());
			$raised = true;
		}

		$this->assertTrue($raised);
	}

	public function testProblemWith() {
		$constraint = $this->f->parallel(array($this->f->isInt(), $this->f->greaterThan(3), $this->f->lessThan(5)));

		$this->assertNull($constraint->problemWith(4));

		$constraint = $this->f->parallel(array($this->f->isInt(), $this->f->greaterThan(3), $this->f->lessThan(1)));
		$this->assertInternalType("string", $constraint->problemWith(2));
		$this->assertEquals("'2' is not greater than '3'.'2' is greater than '1'.", $constraint->problemWith(2));
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

	public function testCorrectErrorMessagesAfterMultiAccept() {
		$constraint = $this->f->parallel(array($this->f->isInt(), $this->f->greaterThan(3), $this->f->lessThan(2)));
		$constraint->accepts(1);
		$constraint->accepts(3);
		$constraint->accepts(4);

		$this->assertEquals("'1' is not greater than '3'.", $constraint->problemWith(1));
		$this->assertEquals("'3' is not greater than '3'.'3' is greater than '2'.", $constraint->problemWith(3));
		$this->assertEquals("'4' is greater than '2'.", $constraint->problemWith(4));
	}
}