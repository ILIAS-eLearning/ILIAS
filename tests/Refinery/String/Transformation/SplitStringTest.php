<?php

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Refinery\Transformation;
use PHPUnit\Framework\TestCase;

/**
 * TestCase for SplitString transformations
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class SplitStringTest extends TestCase {
	const STRING_TO_SPLIT = "I am#a test string#for split";
	protected static $result = array("I am", "a test string", "for split");

	/**
	 * @var Transformation\Transformations\SplitString
	 */
	private $split_string;

	protected function setUp() : void{
		$dataFactory = new \ILIAS\Data\Factory();
		$language = $this->createMock('\ilLanguage');
		$this->f = new \ILIAS\Refinery\Factory($dataFactory, $language);
		$this->split_string = $this->f->string()->splitString("#");
	}

	protected function tearDown(): void {
		$this->f = null;
		$this->split_string = null;
	}

	public function testTransform() {
		$arr = $this->split_string->transform(self::STRING_TO_SPLIT);
		$this->assertEquals(static::$result, $arr);
	}

	public function testTransformFails() {
		$raised = false;
		try {
			$arr = [];
			$next_arr = $this->split_string->transform($arr);
		} catch (InvalidArgumentException $e) {
			$raised = true;
		}
		$this->assertTrue($raised);

		$raised = false;
		try {
			$without = 1001;
			$with = $this->split_string->transform($without);
		} catch (InvalidArgumentException $e) {
			$raised = true;
		}
		$this->assertTrue($raised);

		$raised = false;
		try {
			$std_class = new stdClass();
			$with = $this->split_string->transform($std_class);
		} catch (InvalidArgumentException $e) {
			$raised = true;
		}
		$this->assertTrue($raised);
	}

	public function testInvoke() {
		$split_string = $this->f->string()->splitString("#");
		$arr = $split_string(self::STRING_TO_SPLIT);
		$this->assertEquals(static::$result, $arr);
	}

	public function testInvokeFails() {
		$split_string = $this->f->string()->splitString("#");

		$raised = false;
		try {
			$arr = [];
			$next_arr = $split_string($arr);
		} catch (InvalidArgumentException $e) {
			$raised = true;
		}
		$this->assertTrue($raised);

		$raised = false;
		try {
			$number = 1001;
			$with = $split_string($number);
		} catch (InvalidArgumentException $e) {
			$raised = true;
		}
		$this->assertTrue($raised);

		$raised = false;
		try {
			$std_class = new stdClass();
			$with = $split_string($std_class);
		} catch (InvalidArgumentException $e) {
			$raised = true;
		}
		$this->assertTrue($raised);
	}

	public function testApplyToWithValidValueReturnsAnOkResult() {
		$factory = new \ILIAS\Data\Factory();
		$valueObject = $factory->ok(self::STRING_TO_SPLIT);

		$resultObject = $this->split_string->applyTo($valueObject);

		$this->assertEquals(self::$result, $resultObject->value());
		$this->assertFalse($resultObject->isError());
	}

	public function testApplyToWithInvalidValueWillLeadToErrorResult() {
		$factory = new \ILIAS\Data\Factory();
		$valueObject = $factory->ok(42);

		$resultObject = $this->split_string->applyTo($valueObject);

		$this->assertTrue($resultObject->isError());
	}
}
