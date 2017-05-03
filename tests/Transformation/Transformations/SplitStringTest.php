<?php

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Transformation\Factory;

/**
 * TestCase for SplitString transformations
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class SplitStringTest extends PHPUnit_Framework_TestCase {
	protected function setUp() {
		$this->f = new Transformation\Factory();
	}

	protected function tearDown() {
		$this->f = null;
	}

	public function testTransform() {
		$split_string = $this->f->splitString("#");
		$to_split = array("I am#a test string#for split");
		$arr = $split_string->transform($split_string);
		$this->assertEquals(array("I am", "a test string", "for split"), $arr);

		$raised = false;
		try {
			$next_arr = $split_string->transform($arr);
		} catch (InvalidArgumentException $e) {
			$raised = true;
		}
		$this->assertTrue($raised);

		$raised = false;
		try {
			$without = 1001;
			$with = $split_string->transform($without);
		} catch (InvalidArgumentException $e) {
			$raised = true;
		}
		$this->assertTrue($raised);

		$raised = false;
		try {
			$std_class = new stdClass();
			$with = $split_string->transform($std_class);
		} catch (InvalidArgumentException $e) {
			$raised = true;
		}
		$this->assertTrue($raised);
	}

	public function testInvoke() {
		$split_string = $this->f->splitString("#");
		$to_split = array("I am#a test string#for split");
		$arr = $split_string($split_string);
		$this->assertEquals(array("I am", "a test string", "for split"), $arr);

		$raised = false;
		try {
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
}