<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../../Base.php");

use ILIAS\UI\Implementation\Component\Input;
use \ILIAS\UI\Implementation\Component\Input\Field\InputInternal;
use \ILIAS\UI\Implementation\Component\Input\NameSource;
use \ILIAS\UI\Implementation\Component\Input\InputData;
use \ILIAS\UI\Implementation\Component\Input\Container\Filter\Filter;
use ILIAS\UI\Implementation\Component\SignalGenerator;

use Psr\Http\Message\ServerRequestInterface;


class FixedNameSourceFilter implements NameSource {

	public $name = "name";


	public function getNewName() {
		return $this->name;
	}
}


class ConcreteFilter extends Filter {

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
		$signal_generator = new SignalGenerator();
		$input_factory = new Input\Factory(
			$signal_generator,
			new Input\Field\Factory($signal_generator),
			new Input\Container\Factory()
		);
		$this->input_group = $input_factory->field()->group($inputs);
		$this->inputs = $inputs;
	}


	public function _getPostInput(ServerRequestInterface $request) {
		return $this->getPostInput($request);
	}
}


/**
 * Test on filter implementation.
 */
class FilterTest extends ILIAS_UI_TestBase
{

	protected function buildFactory() {
		return new ILIAS\UI\Implementation\Component\Input\Container\Filter\Factory();
	}

	protected function buildInputFactory() {
		return new ILIAS\UI\Implementation\Component\Input\Field\Factory(new SignalGenerator());
	}

	protected function buildButtonFactory() {
		return new ILIAS\UI\Implementation\Component\Button\Factory;
	}

	protected function buildGlyphFactory() {
		return new ILIAS\UI\Implementation\Component\Glyph\Factory;
	}

	protected function buildPopoverFactory() {
		return new ILIAS\UI\Implementation\Component\Popover\Factory(new SignalGenerator());
	}

	protected function buildLegacyFactory() {
		return new ILIAS\UI\Implementation\Component\Legacy\Legacy("");
	}

	protected function buildListingFactory() {
		return new ILIAS\UI\Implementation\Component\Listing\Factory;
	}

	public function getUIFactory() {
		return new WithNoUIFactories($this->buildButtonFactory(), $this->buildGlyphFactory(), $this->buildPopoverFactory(),
			$this->buildLegacyFactory(), $this->buildListingFactory());
	}

	public function buildDataFactory() {
		return new \ILIAS\Data\Factory;
	}

	public function tearDown() {
		\Mockery::close();
	}


	public function test_getInputs() {
		$f = $this->buildFactory();
		$if = $this->buildInputFactory();
		$name_source = new FixedNameSourceFilter();

		$inputs = [$if->text(""), $if->select("", [])];
		$inputs_rendered = [true, true];
		$filter = $f->standard("#", "#", "#", "#",
			"#", "#", $inputs, $inputs_rendered, false, false);

		$seen_names = [];
		$inputs = $filter->getInputs();

		foreach ($inputs as $input) {
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
		$filter = new ConcreteFilter("#", "#", "#", "#",
			"#", "#", [], [], false, false);
		$request = \Mockery::mock(ServerRequestInterface::class);
		$request->shouldReceive("getParsedBody")->once()->andReturn([]);
		$post_data = $filter->_extractPostData($request);
		$this->assertInstanceOf(InputData::class, $post_data);
	}

	public function test_withRequest() {
		$request = \Mockery::mock(ServerRequestInterface::class);
		$post_data = \Mockery::Mock(InputData::class);
		$post_data->shouldReceive("getOr")->once()->andReturn("");

		$df = $this->buildDataFactory();

		$input_1 = \Mockery::mock(InputInternal::class);
		$input_1->shouldReceive("withInput")->once()->with($post_data)->andReturn($input_1);

		$input_1->shouldReceive("getContent")->once()->andReturn($df->ok(0));

		$input_2 = \Mockery::mock(InputInternal::class);
		$input_2->shouldReceive("withInput")->once()->with($post_data)->andReturn($input_2);

		$input_2->shouldReceive("getContent")->once()->andReturn($df->ok(0));

		$filter = new ConcreteFilter("#", "#", "#", "#",
			"#", "#", [], [], false, false);
		$request = \Mockery::mock(ServerRequestInterface::class);
		$filter->setInputs([$input_1, $input_2]);
		$filter->post_data = $post_data;

		$filter2 = $filter->withRequest($request);

		$this->assertNotSame($filter2, $filter);
		$this->assertInstanceOf(Filter::class, $filter2);
		$this->assertEquals([$input_1, $input_2], $filter2->getInputs());
	}

	public function test_getData() {
		$df = $this->buildDataFactory();
		$request = \Mockery::mock(ServerRequestInterface::class);
		$request->shouldReceive("getParsedBody")->once()->andReturn([]);

		$input_1 = \Mockery::mock(InputInternal::class);
		$input_1->shouldReceive("getContent")->once()->andReturn($df->ok(1));

		$input_1->shouldReceive("withInput")->once()->andReturn($input_1);

		$input_2 = \Mockery::mock(InputInternal::class);
		$input_2->shouldReceive("getContent")->once()->andReturn($df->ok(2));

		$input_2->shouldReceive("withInput")->once()->andReturn($input_2);

		$filter = new ConcreteFilter("#", "#", "#", "#",
			"#", "#", [], [], false, false);
		$filter->setInputs([$input_1, $input_2]);
		$filter = $filter->withRequest($request);
		$this->assertEquals([1, 2], $filter->getData());
	}


	public function test_with_activated() {
		$f = $this->buildFactory();
		$if = $this->buildInputFactory();
		$inputs = [$if->text(""), $if->text("")];
		$inputs_rendered = [true, true];

		$filter = $f->standard("#", "#", "#", "#",
			"#", "#", $inputs, $inputs_rendered, false, false);
		$filter1 = $filter->withActivated();

		$this->assertFalse($filter->isActivated());
		$this->assertTrue($filter1->isActivated());
	}


	public function test_with_deactivated() {
		$f = $this->buildFactory();
		$if = $this->buildInputFactory();
		$inputs = [$if->text(""), $if->text("")];
		$inputs_rendered = [true, true];

		$filter = $f->standard("#", "#", "#", "#",
			"#", "#", $inputs, $inputs_rendered, true, false);
		$filter1 = $filter->withDeactivated();

		$this->assertTrue($filter->isActivated());
		$this->assertFalse($filter1->isActivated());
	}


	public function test_with_expanded() {
		$f = $this->buildFactory();
		$if = $this->buildInputFactory();
		$inputs = [$if->text(""), $if->text("")];
		$inputs_rendered = [true, true];

		$filter = $f->standard("#", "#", "#", "#",
			"#", "#", $inputs, $inputs_rendered, false, false);
		$filter1 = $filter->withExpanded();

		$this->assertFalse($filter->isExpanded());
		$this->assertTrue($filter1->isExpanded());
	}


	public function test_with_collapsed() {
		$f = $this->buildFactory();
		$if = $this->buildInputFactory();
		$inputs = [$if->text(""), $if->text("")];
		$inputs_rendered = [true, true];

		$filter = $f->standard("#", "#", "#", "#",
			"#", "#", $inputs, $inputs_rendered, false, true);
		$filter1 = $filter->withCollapsed();

		$this->assertTrue($filter->isExpanded());
		$this->assertFalse($filter1->isExpanded());
	}
}
