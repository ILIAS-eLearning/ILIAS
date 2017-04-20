<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use namespace ILIAS\Data;

/**
 * Tests working with result object
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilResultTests extends PHPUnit_Framework_TestCase {
	protected function setUp() {
		$this->f = new Data\Factory();
	}

	protected function tearDown() {
		$this->f = null;
	}

	public function testValue() {
		$result = $this->f->ok(3.154);
		$this->assertEquals(3.154, $result->value());
	}

	public function testNoValue() {
		$result = $this->f->error("Something went wrong");
		$this->expectException(Exception::class);
		$result->value();
	}

	public function testIsOk() {
		$result = $this->f->ok(3.154);
		$this->assertTrue($result->isOk());
		$this->assertFalse($result->isError());
	}

	public function testError() {
		$result = $this->f->error("Something went wrong");
		$this->assertEquals("Something went wrong", $result->error());
	}

	public function testNoError() {
		$result = $this->f->ok(3.154);
		$this->expectException(LogicException::class);
		$result->error();
	}

	public function testIsError() {
		$result = $this->f->error("Something went wrong");
		$this->assertTrue($result->isError());
		$this->assertFalse($result->isOk());
	}

	public function testValueOr() {
		$result = $this->f->ok(3.154);
		$this->assertEquals(3.154, $result->valueOr(5));
	}

	public function testValueOrDefault() {
		$result = $this->f->error("Something went wrong");
		$this->assertEquals(5, $result->valueOr(5));
	}

	public function testMapOk() {
		$result = $this->f->ok(3);
		$multiplicator = 3;
		$new_result = $result->map(function($v) use ($multiplicator) {
			return $v * $multiplicator;
		});


		$this->assertInstanceOf(Data\ilResult::class, $new_result);
		$this->assertNotEquals($result, $new_result);
		$this->assertEquals(9, $new_result->value());
	}

	public function testMapError() {
		$result = $this->f->error("Something went wrong");
		$multiplicator = 3;
		$new_result = $result->map(function($v) use ($multiplicator) {
			return $v * $multiplicator;
		});

		$this->assertEquals($result, $new_result);
	}

	public function testThenOk() {
		$result = $this->f->ok(3);
		$f = $this->f;
		$multiplicator = 3;
		$new_result = $result->then(function($v) use ($f, $multiplicator) {
			$ret = $f->ok(($v * $multiplicator));
			return $ret;
		});

		$this->assertInstanceOf(Data\ilResult::class, $new_result);
		$this->assertNotEquals($result, $new_result);
		$this->assertEquals(9, $new_result->value());
	}

	public function testThenCallableNull() {
		$result = $this->f->ok(3);
		$new_result = $result->then(function($v) {
			return null;
		});

		$this->assertInstanceOf(Data\ilResult::class, $new_result);
		$this->assertEquals($result, $new_result);
	}

	public function testThenError() {
		$result = $this->f->error("Something went wrong");
		$f = $this->f;
		$multiplicator = 3;
		$new_result = $result->then(function($v) use ($f, $multiplicator) {
			$ret = $f->ok(($v * $multiplicator));
			return $ret;
		});

		$this->assertInstanceOf(Data\ilResult::class, $new_result);
		$this->assertEquals($result, $new_result);
	}

	public function testCatchError() {
		$result = $this->f->error("Something went wrong");
		$f = $this->f;
		$exception = "Something else went wrong";

		$new_result = $result->catch(function($v) use ($f, $exception) {
			$ret = $f->error($exception);
			return $ret;
		});

		$this->assertInstanceOf(Data\ilResult::class, $new_result);
		$this->assertNotEquals($result, $new_result);
		$this->assertEquals("Something else went wrong", $new_result->error());
	}

	public function testCatchCallableNull() {
		$result = $this->f->error("Something went wrong");
		$f = $this->f;
		$exception = "Something else went wrong";

		$new_result = $result->catch(function($v) {
			return null;
		});

		$this->assertInstanceOf(Data\ilResult::class, $new_result);
		$this->assertEquals($result, $new_result);
	}

	public function testCatchOk() {
		$result = $this->f->ok(3);

		$f = $this->f;
		$exception = "Something else went wrong";

		$new_result = $result->catch(function($v) use ($f, $exception) {
			$ret = $f->error($exception);
			return $ret;
		});

		$this->assertInstanceOf(Data\ilResult::class, $new_result);
		$this->assertEquals($result, $new_result);
	}
}