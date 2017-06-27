<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Implementation\Component\Input\Input;
use \ILIAS\Data\Factory as DataFactory;
use \ILIAS\Transformation\Factory as TransformationFactory;
use \ILIAS\Validation\Factory as ValidationFactory;
use \ILIAS\Data\Result;

class DefInput extends Input {
	public $value_ok = true;
	protected function isClientSideValueOk($value) {
		return $this->value_ok;
	}

	public $content_from_value = null;
	protected function contentFromValue($value) {
		if ($this->content_from_value === null) {
			return parent::contentFromValue($value);
		}
		return $this->content_from_value;
	}
}

/**
 * Test on input implementation.
 */
class InputTest extends ILIAS_UI_TestBase {
	public function setUp() {
		$this->data_factory = new DataFactory();
		$this->transformation_factory = new TransformationFactory();
		$this->input = new DefInput($this->data_factory, "label", "byline");
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

	public function test_withClientSideValue() {
		$value = "some value";
		$input = $this->input->withClientSideValue($value);
		$this->assertEquals(null, $this->input->getClientSideValue());
		$this->assertEquals($value, $input->getClientSideValue());
		$this->assertNotSame($this->input, $input);
	}

	public function test_withClientSideValue_throws() {
		$this->input->value_ok = false;
		$raised = false;
		try {
			$this->input->withClientSideValue("foo");
			$this->assertFalse("This should not happen.");
		}
		catch (\InvalidArgumentException $e) {
			$raised = true;
		}
		$this->assertTrue($raised);
		$this->assertEquals(null, $this->input->getClientSideValue());
	}

	public function test_withName() {
		$name = "name";
		$input = $this->input->withName($name);
		$this->assertEquals(null, $this->input->getName());
		$this->assertEquals($name, $input->getName());
		$this->assertNotSame($this->input, $input);
	}

	public function test_withClientSideError() {
		$error = "error";
		$input = $this->input->withClientSideError($error);
		$this->assertEquals(null, $this->input->getClientSideError());
		$this->assertEquals($error, $input->getClientSideError());
		$this->assertNotSame($this->input, $input);
	}

	public function test_getContent() {
		$this->assertEquals(null, $this->input->getContent());
	}	

	public function test_withInput() {
		$name = "name";
		$value = "value";
		$input = $this->input->withName($name);
		$values = [$name => $value];

		$input2 = $input->withInput($values);
		$res = $input2->getContent();

		$this->assertInstanceOf(Result::class, $res);
		$this->assertTrue($res->isOk());
		$this->assertEquals($value, $res->value());

		$this->assertNotSame($input, $input2);
		$this->assertEquals($value, $input2->getClientSideValue());
	}

	public function test_only_run_withInput_with_name() {
		$raised = false;
		try {
			$this->input->withInput([]);
			$this->assertFalse("This should not happen.");
		}
		catch (\LogicException $e) {
			$raised = true;
		}
		$this->assertTrue($raised);
	}

	public function test_withInput_contentFromValue_returns_error() {
		$name = "name";
		$msg = "an error message";
		$input = $this->input->withName($name);
		$values = [];

		$error = $this->data_factory->error($msg);
		$input->content_from_value = $error;

		$input2 = $input->withInput($values);
		$res = $input2->getContent();

		$this->assertInstanceOf(Result::class, $res);
		$this->assertEquals($error, $res);

		$this->assertNotSame($input, $input2);
		$this->assertEquals($msg, $input2->getClientSideError());
	}

	public function test_withInput_and_transformation() {
		$name = "name";
		$value = "value";
		$transform_to = "other value";
		$input = $this->input->withName($name);
		$values = [$name => $value];

		$input2 = $input
			->withTransformation($this->transformation_factory->custom(function($v) use ($value, $transform_to) {
				$this->assertEquals($value, $v);
				return $transform_to;
			}))
			->withInput($values);
		$res = $input2->getContent();

		$this->assertInstanceOf(Result::class, $res);
		$this->assertTrue($res->isOk());
		$this->assertEquals($transform_to, $res->value());

		$this->assertNotSame($input, $input2);
		$this->assertEquals($value, $input2->getClientSideValue());
	}

	public function test_withInput_and_transformation_different_order() {
		$name = "name";
		$value = "value";
		$transform_to = "other value";
		$input = $this->input->withName($name);
		$values = [$name => $value];

		$input2 = $input
			->withInput($values)
			->withTransformation($this->transformation_factory->custom(function($v) use ($value, $transform_to) {
				$this->assertEquals($value, $v);
				return $transform_to;
			}));
		$res = $input2->getContent();

		$this->assertInstanceOf(Result::class, $res);
		$this->assertTrue($res->isOk());
		$this->assertEquals($transform_to, $res->value());

		$this->assertNotSame($input, $input2);
		$this->assertEquals($value, $input2->getClientSideValue());
	}
}
