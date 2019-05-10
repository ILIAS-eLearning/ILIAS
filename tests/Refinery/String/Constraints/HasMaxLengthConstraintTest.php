<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */
require_once("libs/composer/vendor/autoload.php");

use ILIAS\Refinery;
use ILIAS\Data;
use PHPUnit\Framework\TestCase;

class HasMaxLengthConstraintTest extends TestCase {
	/**
	 * @var Data\Factory
	 */
	private $df;

	/**
	 * @var \ilLanguage
	 */
	private $lng;

	/**
	 * @var integer
	 */
	private $max_length;

	/**
	 * @var Refinery\Constraint
	 */
	private $c;

	public function setUp(): void{
		$this->df = new Data\Factory();
		$this->lng = $this->createMock(\ilLanguage::class);

		$this->max_length = 2;

		$this->c = new \ILIAS\Refinery\String\Constraints\HasMaxLength(
			$this->max_length,
			$this->df,
			$this->lng
		);
	}

	public function testAccepts1() {
		$this->assertTrue($this->c->accepts("12"));
	}

	public function testAccepts2() {
		$this->assertTrue($this->c->accepts("1"));
	}

	public function testNotAccepts() {
		$this->assertFalse($this->c->accepts("123"));
	}

	public function testCheckSucceed() {
		$this->c->check("12");
		$this->assertTrue(true); // does not throw
	}

	public function testCheckFails() {
		$this->expectException(\UnexpectedValueException::class);
		$this->c->check("123");
	}

	public function testNoProblemWith() {
		$this->assertNull($this->c->problemWith("12"));
	}

	public function testProblemWith() {
		$this->lng
			->expects($this->once())
			->method("txt")
			->with("not_max_length")
			->willReturn("-%s-");

		$this->assertEquals("-2-", $this->c->problemWith("123"));
	}

	public function testRestrictOk() {
		$ok = $this->df->ok("12");

		$res = $this->c->applyTo($ok);
		$this->assertTrue($res->isOk());
	}

	public function testRestrictNotOk() {
		$not_ok = $this->df->ok("123");

		$res = $this->c->applyTo($not_ok);
		$this->assertFalse($res->isOk());
	}

	public function testRestrictError() {
		$error = $this->df->error("error");

		$res = $this->c->applyTo($error);
		$this->assertSame($error, $res);
	}

	public function testWithProblemBuilder() {
		$new_c = $this->c->withProblemBuilder(function() { return "This was a fault"; });
		$this->assertEquals("This was a fault", $new_c->problemWith("123"));
	}
}
