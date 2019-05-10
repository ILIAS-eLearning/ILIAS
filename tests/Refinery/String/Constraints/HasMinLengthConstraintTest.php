<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */
require_once("libs/composer/vendor/autoload.php");

use ILIAS\Refinery\String\Constraints\HasMinLength;
use ILIAS\Refinery;
use ILIAS\Data;
use PHPUnit\Framework\TestCase;

class HasMinLengthConstraintTest extends TestCase {
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
	private $min_length;

	/**
	 * @var Refinery\Constraint
	 */
	private $c;

	public function setUp(): void{
		$this->df = new Data\Factory();
		$this->lng = $this->createMock(\ilLanguage::class);

		$this->min_length = 10;

		$this->c = new HasMinLength(
			$this->min_length,
			$this->df,
			$this->lng
		);
	}

	public function testAccepts1() {
		$this->assertTrue($this->c->accepts("1234567890"));
	}

	public function testAccepts2() {
		$this->assertTrue($this->c->accepts("12345678901"));
	}

	public function testNotAccepts() {
		$this->assertFalse($this->c->accepts("123456789"));
	}

	public function testCheckSucceed() {
		$this->c->check("1234567890");
		$this->assertTrue(true); // does not throw
	}

	public function testCheckFails() {
		$this->expectException(\UnexpectedValueException::class);
		$this->c->check("");
	}

	public function testNoProblemWith() {
		$this->assertNull($this->c->problemWith("1234567890"));
	}

	public function testProblemWith() {
		$this->lng
			->expects($this->once())
			->method("txt")
			->with("not_min_length")
			->willReturn("-%s-%s-");

		$this->assertEquals("-3-10-", $this->c->problemWith("123"));
	}

	public function testRestrictOk() {
		$ok = $this->df->ok("1234567890");

		$res = $this->c->applyTo($ok);
		$this->assertTrue($res->isOk());
	}

	public function testRestrictNotOk() {
		$not_ok = $this->df->ok("1234");

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
		$this->assertEquals("This was a fault", $new_c->problemWith(""));
	}
}
