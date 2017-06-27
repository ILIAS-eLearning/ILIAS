<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Implementation\Component\Input\Input;

class DefInput extends Input {
}

/**
 * Test on input implementation.
 */
class InputTest extends ILIAS_UI_TestBase {
	public function setUp() {
		$this->input = new DefInput("label", "byline");
	}

	public function test_constructor() {
		$this->assertEquals("label", $this->input->getLabel());
		$this->assertEquals("byline", $this->input->getByline());
	}

	public function test_withLabel() {
		$input = $this->input->withLabel("new label");
		$this->assertEquals("new label", $input->getLabel());
		$this->assertNotSame($this->input, $input);
	}

	public function test_withByline() {
		$input = $this->input->withByline("new byline");
		$this->assertEquals("new byline", $input->getByline());
		$this->assertNotSame($this->input, $input);
	}
}
