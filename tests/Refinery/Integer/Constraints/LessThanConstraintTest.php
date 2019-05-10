<?php

namespace ILIAS\Tests\Refinery\Integer\Constraints;

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */
require_once("libs/composer/vendor/autoload.php");

use ILIAS\Refinery;
use ILIAS\Data;
use PHPUnit\Framework\TestCase;

class LessThanConstraintTest extends TestCase {

	/**
	 * @var \ILIAS\Refinery\Integer\Constraints\LessThan
	 */
	private $c;

	/**
	 * @var
	 */
	private $lng;

	/**
	 * @var Data\Factory
	 */
	private $df;

	/**
	 * @var integer
	 */
	private $less_than;

	public function setUp(): void{
		$this->df = new Data\Factory();
		$this->lng = $this->getMockBuilder(\ilLanguage::class)
			->disableOriginalConstructor()
			->getMock();

		$this->less_than = 10;

		$this->c = new \ILIAS\Refinery\Integer\Constraints\LessThan(
			$this->less_than,
			$this->df,
			$this->lng
		);
	}

	public function testAccepts() {
		$this->assertTrue($this->c->accepts(2));
	}

	public function testNotAccepts() {
		$this->assertFalse($this->c->accepts(10));
	}

	public function testCheckSucceed() {
		$this->c->check(2);
		$this->assertTrue(true); // does not throw
	}

	public function testCheckFails() {
		$this->expectException(\UnexpectedValueException::class);
		$this->c->check(11);
	}

	public function testNoProblemWith() {
		$this->assertNull($this->c->problemWith(1));
	}

	public function testProblemWith() {
		$this->lng
			->expects($this->once())
			->method("txt")
			->with("not_less_than")
			->willReturn("-%s-%s-");

		$this->assertEquals("-12-{$this->less_than}-", $this->c->problemWith("12"));
	}

	public function testRestrictOk() {
		$ok = $this->df->ok(1);

		$res = $this->c->applyTo($ok);
		$this->assertTrue($res->isOk());
	}

	public function testRestrictNotOk() {
		$not_ok = $this->df->ok(1234);

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
		$this->assertEquals("This was a fault", $new_c->problemWith(13));
	}
}
