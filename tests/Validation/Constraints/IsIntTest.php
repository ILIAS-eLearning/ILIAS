<?php

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Validation;
use ILIAS\Data;

/**
 * TestCase for the factory of constraints
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class IsIntTest extends PHPUnit_Framework_TestCase {
	protected function setUp() {
		$this->f = new Validation\Factory();
		$this->is_int = $this->f->isInt();

		$this->rf = new Data\Factory();
		$this->ok = $this->rf->ok(2);
		$this->ok2 = $this->rf->ok(2.2);
		$this->error = $this->rf->error("text");
	}

	protected function tearDown() {
		$this->f = null;
		$this->is_int = null;
		$this->rf = null;
		$this->ok = null;
		$this->ok2 = null;
		$this->error = null;
	}

	public function testAccept() {
		$this->assertTrue($this->is_int->accepts(2));
		$this->assertFalse($this->is_int->accepts(2.2));
	}

	public function testCheck() {
		$raised = false;
		try {
			$this->is_int->check(2);
		} catch (UnexpectedValueException $e) {
			$raised = true;
		}

		$this->assertFalse($raised);

		try {
			$this->is_int->check(2.5);
		} catch (UnexpectedValueException $e) {
			$raised = true;
		}

		$this->assertTrue($raised);
	}

	public function testProblemWith() {
		$this->assertInternalType("string", $this->is_int->problemWith(2.2));
		$this->asserNull($this->is_int->problemWith(2));
	}

	public function testRestrict() {
		$result = $this->is_int->restrict($this->ok);
		$this->assertTrue($result->isOk());

		$result = $this->is_int->restrict($this->ok2);
		$this->assertTrue($result->isError());

		$result = $this->is_int->restrict($this->error);
		$this->asserSame($this->error, $result);
	}