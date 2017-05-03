<?php

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Transformation\Factory;

/**
 * TestCase for Custom transformations
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class CustomTest extends PHPUnit_Framework_TestCase {
	protected function setUp() {
		$this->f = new Transformation\Factory();
	}

	protected function tearDown() {
		$this->f = null;
	}

	public function testTransform() {
		$string_to_lower = $this->f->custom(function($value) { 
			if(!is_string($value)) {
				throw new InvalidArgumentException("Value was not a string.");
			}
			return strtolower($value);}
		);

		$upper_string = "I Am A Test String.";
		$lower_string = $string_to_lower->transform($upper_string);
		$this->assertEquals("i am a test string.", $arr);

		$raised = false;
		try {
			$lower_string = $string_to_lower->transform(array());
		} catch (InvalidArgumentException $e) {
			$this->assertEquals("Value was not a string", $e->getMessage());
			$raised = true;
		}
		$this->assertTrue($raised);

		$raised = false;
		try {
			$lower_string = $string_to_lower->transform(12345);
		} catch (InvalidArgumentException $e) {
			$this->assertEquals("Value was not a string", $e->getMessage());
			$raised = true;
		}
		$this->assertTrue($raised);

		$raised = false;
		try {
			$std_class = new stdClass();
			$lower_string = $split_string->transform($std_class);
		} catch (InvalidArgumentException $e) {
			$this->assertEquals("Value was not a string", $e->getMessage());
			$raised = true;
		}
		$this->assertTrue($raised);
	}

	public function testInvoke() {
		$string_to_lower = $this->f->custom(function($value) { 
			if(!is_string($value)) {
				throw new InvalidArgumentException("Value was not a string.");
			}
			return strtolower($value);}
		);

		$upper_string = "I Am A Test String.";
		$lower_string = $string_to_lower($upper_string);
		$this->assertEquals("i am a test string.", $arr);

		$raised = false;
		try {
			$lower_string = $string_to_lower(array());
		} catch (InvalidArgumentException $e) {
			$this->assertEquals("Value was not a string", $e->getMessage());
			$raised = true;
		}
		$this->assertTrue($raised);

		$raised = false;
		try {
			$lower_string = $string_to_lower(12345);
		} catch (InvalidArgumentException $e) {
			$this->assertEquals("Value was not a string", $e->getMessage());
			$raised = true;
		}
		$this->assertTrue($raised);

		$raised = false;
		try {
			$std_class = new stdClass();
			$lower_string = $split_string($std_class);
		} catch (InvalidArgumentException $e) {
			$this->assertEquals("Value was not a string", $e->getMessage());
			$raised = true;
		}
		$this->assertTrue($raised);
	}
}