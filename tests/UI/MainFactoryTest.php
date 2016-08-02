<?php

require_once 'tests/UI/AbstractFactoryTest.php';

class MainFactoryTest extends AbstractFactoryTest {
	public $kitchensink_info_settings = array();

	public $factory_title = 'ILIAS\\UI\\Factory';

	public function test_proper_namespace() {
		// Nothing to test here.
	}

	public function test_proper_name() {
		// Nothing to test here.
	}

	protected function get_regex_factory_namespace() {
		return "\\\\ILIAS\\\\UI\\\\Component";
	}
}
