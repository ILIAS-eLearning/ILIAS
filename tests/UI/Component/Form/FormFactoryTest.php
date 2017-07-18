<?php

require_once 'tests/UI/AbstractFactoryTest.php';

use \ILIAS\UI\Component\Form;

class FormFactoryTest extends AbstractFactoryTest {
	public $kitchensink_info_settings = array
		(
		);

	public $factory_title = 'ILIAS\\UI\\Component\\Form\\Factory';

	final public function buildFactory() {
		return new \ILIAS\UI\Implementation\Component\Form\Factory;
	}

	public function test_implements_factory_interface() {
	    $f = $this->buildFactory();

		$form = $f->standard("#", []);
		$this->assertInstanceOf(Form\Form::class, $form);
		$this->assertInstanceOf(Form\Standard::class, $form);
	}
}
