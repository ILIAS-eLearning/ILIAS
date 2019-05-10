<?php

/* Copyright (c) 2017, 2018, Stefan Hecken <stefan.hecken@concepts-and-training.de>, Richard Klees <richard.klees@concepts-and-training.de, Extended GPL, see docs/LICENSE */
require_once("libs/composer/vendor/autoload.php");

use ILIAS\Refinery\Validation;
use ILIAS\Data;
use PHPUnit\Framework\TestCase;

class NotTest extends TestCase {
	/**
	 * @var Data\Factory
	 */
	private $df;

	/**
	 * @var \ilLanguage
	 */
	private $lng;

	/**
	 * @var \ILIAS\Refinery\Factory
	 */
	private $refinery;

	/**
	 * @var
	 */
	private $not_true;

	/**
	 * @var
	 */
	private $not_false;

	public function setUp(): void{
		$this->df = new Data\Factory();
		$this->lng = $this->createMock(\ilLanguage::class);
		$this->refinery = new \ILIAS\Refinery\Factory($this->df, $this->lng);

		$group = $this->refinery->custom();

		$this->not_true = $this->refinery->logical()->not($group->constraint(
			function($v) { return true; },
			"not_true"
		));

		$this->not_false = $this->refinery->logical()->not($group->constraint(
			function($v) { return false; },
			"not_false"
		));
	}

	public function testAccepts() {
		$this->assertTrue($this->not_false->accepts(null));
	}

	public function testNotAccepts() {
		$this->assertFalse($this->not_true->accepts(null));
	}

	public function testCheckSucceed() {
		$this->not_false->check(null);
		$this->assertTrue(true); // does not throw
	}

	public function testCheckFails() {
		$this->expectException(\UnexpectedValueException::class);
		$this->not_true->check(null);
	}

	public function testNoProblemWith() {
		$this->assertNull($this->not_false->problemWith(null));
	}

	public function testProblemWith() {
		$this->lng
			->expects($this->once())
			->method("txt")
			->with("not_generic")
			->willReturn("-%s-");

		$this->assertEquals("-not_true-", $this->not_true->problemWith(null));
	}

	public function testRestrictOk() {
		$ok = $this->df->ok(null);

		$res = $this->not_false->applyTo($ok);
		$this->assertTrue($res->isOk());
	}

	public function testRestrictNotOk() {
		$not_ok = $this->df->ok(null);

		$res = $this->not_true->applyTo($not_ok);
		$this->assertFalse($res->isOk());
	}

	public function testRestrictError() {
		$error = $this->df->error("error");

		$res = $this->not_false->applyTo($error);
		$this->assertSame($error, $res);
	}

	public function testWithProblemBuilder() {
		$new_c = $this->not_true->withProblemBuilder(function() { return "This was a fault"; });
		$this->assertEquals("This was a fault", $new_c->problemWith(null));
	}
}
