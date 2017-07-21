<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Implementation\Component\Input\Input;
use \ILIAS\UI\Implementation\Component\Input\NameSource;
use \ILIAS\UI\Implementation\Component\Input\PostData;
use \ILIAS\UI\Implementation\Component\Form\Form;

use Psr\Http\Message\ServerRequestInterface;

class FixedNameSource implements NameSource {
	public $name = "name";
	public function getNewName() {
		return $this->name;
	}
}

class ConcreteForm extends Form {
	public $post_data = null;
	public function _extractPostData(ServerRequestInterface $request) {
		return $this->extractPostData($request);
	}
	public function extractPostData(ServerRequestInterface $request) {
		if ($this->post_data !== null) {
			return $this->post_data;
		}
		return parent::extractPostData($request);
	}
}

/**
 * Test on form implementation.
 */
class FormTest extends ILIAS_UI_TestBase {
	protected function buildFactory() {
		return new ILIAS\UI\Implementation\Component\Form\Factory;
	}

	protected function buildInputFactory() {
		return new ILIAS\UI\Implementation\Component\Input\Factory;
	}

	protected function buildButtonFactory() {
		return new ILIAS\UI\Implementation\Component\Button\Factory;
	}

	public function getUIFactory() {
		return new WithButtonNoUIFactory($this->buildButtonFactory());
	}

	public function test_getInputs () {
	    $f = $this->buildFactory();
		$if = $this->buildInputFactory();
		$form = new ConcreteForm([$if->text("label")]);
		$this->assertEquals([$if->text("label")], $form->getInputs());
	}

	public function test_getNamedInputs () {
	    $f = $this->buildFactory();
		$if = $this->buildInputFactory();
		$name_source = new FixedNameSource();

		$inputs = [$if->text(""), $if->text("")];
		$form = new ConcreteForm($inputs);

		$seen_names = [];
		$named_inputs = $form->getNamedInputs();
		$this->assertEquals(count($inputs), count($named_inputs));

		foreach($named_inputs as $named_input) {
			$name = $named_input->getName();
			$name_source->name = $name;

			// name is a string
			$this->assertInternalType("string", $name);

			// only name is attached
			$input = array_shift($inputs);
			$this->assertEquals($input->withNameFrom($name_source), $named_input);

			// every name can only be contained once.
			$this->assertNotContains($name, $seen_names);
			$seen_names[] = $name;
		}
	}

	public function test_extractPostData() {
		$form = new ConcreteForm([]);
		$request = \Mockery::mock(ServerRequestInterface::class);
		$request
			->shouldReceive("getParsedBody")->once()
			->andReturn([]);
		$post_data = $form->_extractPostData($request);
		$this->assertInstanceOf(PostData::class, $post_data);
	}

	public function test_getPostInput() {
		$request = \Mockery::mock(ServerRequestInterface::class);
	}
}
