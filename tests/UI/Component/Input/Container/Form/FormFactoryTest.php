<?php

require_once 'tests/UI/AbstractFactoryTest.php';

use \ILIAS\UI\Component\Input\Container\Form;

class FormFactoryTest extends AbstractFactoryTest {

	public $kitchensink_info_settings = array(
		"standard" => array(
			"context" => false,
		),
	);
	public $factory_title = 'ILIAS\\UI\\Component\\Input\\Container\\Form\\Factory';


	final public function buildFactory() {
		return new \ILIAS\UI\Implementation\Component\Input\Container\Form\Factory;
	}


	public function test_implements_factory_interface() {
		$f = $this->buildFactory();

		$form = $f->standard("#", []);
		$this->assertInstanceOf(Form\Form::class, $form);
		$this->assertInstanceOf(Form\Standard::class, $form);
	}
}
