<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Implementation\Component\Input\Input;
use \ILIAS\Data\Factory as DataFactory;
use \ILIAS\Data\Result;

class DefInput extends Input {
	public $value_ok = true;
	protected function isValueOk($value) {
		return $this->value_ok;
	}
}

/**
 * Test on input implementation.
 */
class InputTest extends ILIAS_UI_TestBase {
	public function setUp() {
		$data_factory = new DataFactory();
		$this->input = new DefInput($data_factory, "label", "byline");
	}

	public function test_constructor() {
		$this->assertEquals("label", $this->input->getLabel());
		$this->assertEquals("byline", $this->input->getByline());
	}

	public function test_withLabel() {
		$label = "new label";
		$input = $this->input->withLabel($label);
		$this->assertEquals($label, $input->getLabel());
		$this->assertNotSame($this->input, $input);
	}

	public function test_withByline() {
		$byline = "new byline";
		$input = $this->input->withByline($byline);
		$this->assertEquals($byline, $input->getByline());
		$this->assertNotSame($this->input, $input);
	}

	public function test_withValue() {
		$value = "some value";
		$input = $this->input->withValue($value);
		$this->assertEquals(null, $this->input->getValue());
		$this->assertEquals($value, $input->getValue());
		$this->assertNotSame($this->input, $input);
	}

	public function test_withValue_throws() {
		$this->input->value_ok = false;
		$raised = false;
		try {
			$this->input->withValue("foo");
			$this->assertFalse("This should not happen.");
		}
		catch (\InvalidArgumentException $e) {
			$raised = true;
		}
		$this->assertTrue($raised);
		$this->assertEquals(null, $this->input->getValue());
	}

	public function test_withName() {
		$name = "name";
		$input = $this->input->withName($name);
		$this->assertEquals(null, $this->input->getName());
		$this->assertEquals($name, $input->getName());
		$this->assertNotSame($this->input, $input);
	}

	public function test_withError() {
		$error = "error";
		$input = $this->input->withError($error);
		$this->assertEquals(null, $this->input->getError());
		$this->assertEquals($error, $input->getError());
		$this->assertNotSame($this->input, $input);
	}

	public function test_collect() {
		$name = "name";
		$value = "value";
		$input = $this->input->withName($name);
		$values = [$name => $value];

		list($res,$input2) = $input->collect($values);

		$this->assertInstanceOf(Result::class, $res);
		$this->assertTrue($res->isOk());
		$this->assertEquals($value, $res->value());

		$this->assertNotSame($input, $input2);
		$this->assertEquals($value, $input2->getValue());
	}

	public function test_only_collect_with_name() {
		$raised = false;
		try {
			$this->input->collect([]);
			$this->assertFalse("This should not happen.");
		}
		catch (\LogicException $e) {
			$raised = true;
		}
		$this->assertTrue($raised);
	}
}
