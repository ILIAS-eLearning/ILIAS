<?php

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Validation;
use ILIAS\Data;

/**
 * TestCase for the factory of constraints
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ValidationFactoryTest extends PHPUnit_Framework_TestCase {
	protected function setUp() {
		$this->f = new Validation\Factory(new Data\Factory());
	}

	protected function tearDown() {
		$this->f = null;
	}

	public function testIsInt() {
		$is_int = $this->f->isInt();
		$this->assertInstanceOf(Validation\Constraint::class, $is_int);
	}

	public function testGreaterThan() {
		$gt = $this->f->greaterThan(5);
		$this->assertInstanceOf(Validation\Constraint::class, $gt);
	}

	public function testLessThan() {
		$lt = $this->f->lessThan(5);
		$this->assertInstanceOf(Validation\Constraint::class, $lt);
	}

	public function testCustom() {
		$custom = $this->f->custom(function ($value) { return "This was fault";}, 5);
		$this->assertInstanceOf(Validation\Constraint::class, $custom);
	}

	public function testSequential() {
		$constraints = array(
				$this->f->greaterThan(5),
				$this->f->lessThan(15)
			);

		$sequential = $this->f->sequential($constraints);
		$this->assertInstanceOf(Validation\Constraint::class, $sequential);
	}

	public function testParallel() {
		$constraints = array(
				$this->f->greaterThan(5),
				$this->f->lessThan(15)
			);

		$parallel = $this->f->parallel($constraints);
		$this->assertInstanceOf(Validation\Constraint::class, $parallel);
	}

	public function testNot() {
		$constraint = $this->f->greaterThan(5);
		$not = $this->f->not($constraint);
		$this->assertInstanceOf(Validation\Constraint::class, $not);
	}
}
