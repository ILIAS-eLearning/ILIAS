<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Implementation\Component\Input\Input;
use \ILIAS\UI\Implementation\Component\Input\InputInternal;
use \ILIAS\UI\Implementation\Component\Input\NameSource;
use \ILIAS\UI\Implementation\Component\Input\PostData;
use \ILIAS\UI\Implementation\Component\Form\Form;
use \ILIAS\Transformation\Factory as TransformationFactory;

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
	public function setInputs(array $inputs) {
		$this->inputs = $inputs;
	}
	public function _getPostInput(ServerRequestInterface $request) {
		return $this->getPostInput($request);
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

	protected function buildTransformation(\Closure $trafo) {
		$f = new TransformationFactory();
		return $f->custom($trafo);
	}

	public function getUIFactory() {
		return new WithButtonNoUIFactory($this->buildButtonFactory());
	}

	public function tearDown() {
		\Mockery::close();
	}

	public function test_getInputs () {
	    $f = $this->buildFactory();
		$if = $this->buildInputFactory();
		$name_source = new FixedNameSource();

		$inputs = [$if->text(""), $if->text("")];
		$form = new ConcreteForm($inputs);

		$seen_names = [];
		$inputs = $form->getInputs();
		$this->assertEquals(count($inputs), count($inputs));

		foreach($inputs as $input) {
			$name = $input->getName();
			$name_source->name = $name;

			// name is a string
			$this->assertInternalType("string", $name);

			// only name is attached
			$input = array_shift($inputs);
			$this->assertEquals($input->withNameFrom($name_source), $input);

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


	public function test_withRequest() {
		$request = \Mockery::mock(ServerRequestInterface::class);
		$post_data = \Mockery::Mock(PostData::class);

		$input_1 = \Mockery::mock(InputInternal::class);
		$input_1
			->shouldReceive("withInput")->once()
			->with($post_data)
			->andReturn("one");

		$input_2 = \Mockery::mock(InputInternal::class);
		$input_2
			->shouldReceive("withInput")->once()
			->with($post_data)
			->andReturn("two");

		$form = new ConcreteForm([]);
		$form->setInputs([$input_1, $input_2]);
		$form->post_data = $post_data;

		$form2 = $form->withRequest($request);

		$this->assertNotSame($form2, $form);
		$this->assertInstanceOf(Form::class, $form2);
		$this->assertEquals(["one", "two"], $form2->getInputs());
	}

	public function test_getData() {
		$request = \Mockery::mock(ServerRequestInterface::class);
		$post_data = \Mockery::Mock(PostData::class);

		$input_1 = \Mockery::mock(InputInternal::class);
		$input_1
			->shouldReceive("getContent")->once()
			->andReturn(1);

		$input_2 = \Mockery::mock(InputInternal::class);
		$input_2
			->shouldReceive("getContent")->once()
			->andReturn(2);

		$form = new ConcreteForm([]);
		$form->setInputs([$input_1, $input_2]);

		$this->assertEquals([1,2], $form->getData());
	}

	public function test_withTransformation() {
		$request = \Mockery::mock(ServerRequestInterface::class);
		$post_data = \Mockery::Mock(PostData::class);

		$input_1 = \Mockery::mock(InputInternal::class);
		$input_1
			->shouldReceive("getContent")->once()
			->andReturn(1);

		$input_2 = \Mockery::mock(InputInternal::class);
		$input_2
			->shouldReceive("getContent")->once()
			->andReturn(2);

		$form = new ConcreteForm([]);
		$form->setInputs([$input_1, $input_2]);

		$form2 = $form->withTransformation($this->buildTransformation(function ($v) {
			return "transformed";
		}));

		$this->assertNotSame($form2, $form);
		$this->assertEquals("transformed", $form2->getData());
	}

	public function test_getPostInput() {
		$request = \Mockery::mock(ServerRequestInterface::class);
		$post_data = \Mockery::Mock(PostData::class);

		$input_1 = \Mockery::mock(InputInternal::class);
		$input_1
			->shouldReceive("withInput")->once()
			->with($post_data)
			->andReturn($input_1);
		$input_1
			->shouldReceive("getContent")->once()
			->andReturn(1);

		$input_2 = \Mockery::mock(InputInternal::class);
		$input_2
			->shouldReceive("withInput")->once()
			->with($post_data)
			->andReturn($input_2);
		$input_2
			->shouldReceive("getContent")->once()
			->andReturn(2);

		$form = new ConcreteForm([]);
		$form->post_data = $post_data;
		$form->setInputs([$input_1, $input_2]);

		$content = $form->_getPostInput($request);
	}
}
