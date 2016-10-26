<?php

require_once 'tests/UI/AbstractFactoryTest.php';

class ButtonFactoryTest extends AbstractFactoryTest {
	public $kitchensink_info_settings = array
		( "standard"	=> array("context" => false)
		, "close"		=> array("context" => false)
		);

	public $factory_title = 'ILIAS\\UI\\Component\\Button\\Factory';
}
