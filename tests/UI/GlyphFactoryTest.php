<?php

require_once 'tests/UI/AbstractFactoryTest.php';

class GlyphFactoryTest extends AbstractFactoryTest {
	public $kitchensink_info_settings = array(
		"previous" => array("rules" => false)
		,"next" => array("rules" => false)
		,"calendar" => array("context" => false, "description" => false, "rules" => false)
		,"close" => array("context" => false, "description" => false, "rules" => false)
		,"attachment" => array("context" => false, "description" => false, "rules" => false)
		,"caret" => array("context" => false, "rules" => false)
		,"drag" => array("context" => false, "description" => false, "rules" => false)
		,"search" => array("context" => false, "rules" => false)
		,"filter" => array("context" => false, "description" => false, "rules" => false)
		,"info" => array("context" => false, "description" => false, "rules" => false)
		,"envelope" => array("context" => false, "description" => false, "rules" => false)
		);

	public static $factory_title  = 'ILIAS\\UI\\Component\\Glyph\\Factory';
}