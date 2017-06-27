<?php

require_once 'tests/UI/AbstractFactoryTest.php';

class TextInputTest extends AbstractFactoryTest {
	public $kitchensink_info_settings = array
		(
		);

	public $factory_title = 'ILIAS\\UI\\Component\\Input\\Factory';

	public function test_implements_factory_interface() {
	    $f = $this->buildFactory();

		$text = $f->text("label", "byline");
	}
}
