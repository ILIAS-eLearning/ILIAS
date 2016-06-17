<?php

require_once 'tests/UI/AbstractFactoryTest.php';

class GlyphFactoryTest extends AbstractFactoryTest {
	protected $kitchensink_info_settings = array(
		"up" => array(
			'description' 	=> 1
			,'context'		=> 1
			,'background' 	=> 0
			,'featurewiki'	=> 1
			,'javascript'	=> 0
			,'rules'		=> 1)
		,"down" => array(
			'description' 	=> 1
			,'context'		=> 1
			,'background' 	=> 0
			,'featurewiki'	=> 1
			,'javascript'	=> 0
			,'rules'		=> 1)
		,"add" => array(
			'description' 	=> 1
			,'context'		=> 1
			,'background' 	=> 0
			,'featurewiki'	=> 0
			,'javascript'	=> 0
			,'rules'		=> 1)
		,"remove" => array(
			'description' 	=> 1
			,'context'		=> 1
			,'background' 	=> 0
			,'featurewiki'	=> 0
			,'javascript'	=> 0
			,'rules'		=> 0)
		,"previous" => array(
			'description' 	=> 1
			,'context'		=> 1
			,'background' 	=> 0
			,'featurewiki'	=> 0
			,'javascript'	=> 0
			,'rules'		=> 0)
		,"next" => array(
			'description' 	=> 1
			,'context'		=> 1
			,'background' 	=> 0
			,'featurewiki'	=> 0
			,'javascript'	=> 0
			,'rules'		=> 0)
		,"calendar" => array(
			'description' 	=> 0
			,'context'		=> 0
			,'background' 	=> 0
			,'featurewiki'	=> 0
			,'javascript'	=> 0
			,'rules'		=> 0)
		,"close" => array(
			'description' 	=> 0
			,'context'		=> 0
			,'background' 	=> 0
			,'featurewiki'	=> 0
			,'javascript'	=> 0
			,'rules'		=> 0)
		,"attachment" => array(
			'description' 	=> 0
			,'context'		=> 0
			,'background' 	=> 0
			,'featurewiki'	=> 0
			,'javascript'	=> 0
			,'rules'		=> 0)
		,"caret" => array(
			'description' 	=> 0
			,'context'		=> 0
			,'background' 	=> 0
			,'featurewiki'	=> 0
			,'javascript'	=> 0
			,'rules'		=> 0)
		,"drag" => array(
			'description' 	=> 0
			,'context'		=> 0
			,'background' 	=> 0
			,'featurewiki'	=> 0
			,'javascript'	=> 0
			,'rules'		=> 0)
		,"search" => array(
			'description' 	=> 1
			,'context'		=> 0
			,'background' 	=> 0
			,'featurewiki'	=> 0
			,'javascript'	=> 0
			,'rules'		=> 0)
		,"filter" => array(
			'description' 	=> 0
			,'context'		=> 0
			,'background' 	=> 0
			,'featurewiki'	=> 0
			,'javascript'	=> 0
			,'rules'		=> 0)
		,"info" => array(
			'description' 	=> 0
			,'context'		=> 0
			,'background' 	=> 0
			,'featurewiki'	=> 0
			,'javascript'	=> 0
			,'rules'		=> 0)
		,"envelope" => array(
			'description' 	=> 0
			,'context'		=> 0
			,'background' 	=> 0
			,'featurewiki'	=> 0
			,'javascript'	=> 0
			,'rules'		=> 0)
		);

	public static function getFactoryTitle() {
		return 'ILIAS\\UI\\Component\\Glyph\\Factory';
	}
}