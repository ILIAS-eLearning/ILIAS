<?php

require_once 'tests/UI/AbstractFactoryTest.php';

use \ILIAS\UI\Component\Input;

class InputFactoryTest extends AbstractFactoryTest {
	public $kitchensink_info_settings = array
		(
		);

	public $factory_title = 'ILIAS\\UI\\Component\\Input\\Factory';

	final public function buildFactory() {
		return new \ILIAS\UI\Implementation\Component\Input\Factory;
	}

	public function test_implements_factory_interface() {
	    $f = $this->buildFactory();

		$text = $f->text("label", "byline");
		$this->assertInstanceOf(Input\Input::class, $text);
		$this->assertInstanceOf(Input\Text::class, $text);
	}
}
