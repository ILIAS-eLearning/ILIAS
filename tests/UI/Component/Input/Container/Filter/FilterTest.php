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
use \ILIAS\Data;
use \ILIAS\Refinery\Validation;
use \ILIAS\Refinery\Transformation;

use Psr\Http\Message\ServerRequestInterface;


class FixedNameSourceFilter implements NameSource {

	public $name = "name";


	public function getNewName() {
		return $this->name;
	}
}


class ConcreteFilter extends Filter {

	public $input_data = null;

	public function __construct(SignalGenerator $signal_generator, Input\Field\Factory $field_factory, $toggle_action_on, $toggle_action_off, $expand_action, $collapse_action, $apply_action, $reset_action, array $inputs, array $is_input_rendered, $is_activated, $is_expanded)
	{
		$this->input_factory = $field_factory;
		parent::__construct($signal_generator, $field_factory, $toggle_action_on, $toggle_action_off, $expand_action, $collapse_action, $apply_action, $reset_action, $inputs, $is_input_rendered, $is_activated, $is_expanded);
	}


	public function _extractParamData(ServerRequestInterface $request) {
		return $this->extractParamData($request);
	}


	public function extractParamData(ServerRequestInterface $request) {
		if ($this->input_data !== null) {
			return $this->input_data;
		}

		return parent::extractParamData($request);
	}


	public function setInputs(array $inputs) {
		$this->input_group = $this->input_factory->group($inputs);
		$this->inputs = $inputs;
	}


	public function _getInput(ServerRequestInterface $request) {
		return $this->getInput($request);
	}
}


/**
 * Test on filter implementation.
 */
class FilterTest extends ILIAS_UI_TestBase
{

	protected function buildFactory() {
		return new ILIAS\UI\Implementation\Component\Input\Container\Filter\Factory(
			new SignalGenerator(),
			$this->buildInputFactory());
	}

	protected function buildInputFactory() {
		$df = new Data\Factory();
		$language = $this->createMock(\ilLanguage::class);
		return new ILIAS\UI\Implementation\Component\Input\Field\Factory(
			new SignalGenerator(),
			$df,
			new Validation\Factory($df, $language),
			new Transformation\Factory(),
			new ILIAS\Refinery\Factory($df, $language)
		);
	}

	protected function buildButtonFactory() {
		return new ILIAS\UI\Implementation\Component\Button\Factory;
	}

	protected function buildGlyphFactory() {
		return new ILIAS\UI\Implementation\Component\Symbol\Glyph\Factory;
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

	public function tearDown(): void {
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
			$this->assertIsString($name);

			// only name is attached
			$input = array_shift($inputs);
			$this->assertEquals($input->withNameFrom($name_source), $input);

			// every name can only be contained once.
			$this->assertNotContains($name, $seen_names);
			$seen_names[] = $name;
		}
	}

	public function test_extractParamData() {
		$filter = new ConcreteFilter(new SignalGenerator(), $this->buildInputFactory(),
			"#", "#", "#", "#",
			"#", "#", [], [], false, false);
		$request = \Mockery::mock(ServerRequestInterface::class);
		$request->shouldReceive("getQueryParams")->once()->andReturn([]);
		$input_data = $filter->_extractParamData($request);
		$this->assertInstanceOf(InputData::class, $input_data);
	}

	public function test_withRequest() {
		$request = \Mockery::mock(ServerRequestInterface::class);
		$input_data = \Mockery::Mock(InputData::class);
		$input_data->shouldReceive("getOr")->once()->andReturn("");

		$df = $this->buildDataFactory();

		$input_1 = \Mockery::mock(InputInternal::class);
		$input_1->shouldReceive("withInput")->once()->with($input_data)->andReturn($input_1);

		$input_1->shouldReceive("getContent")->once()->andReturn($df->ok(0));

		$input_2 = \Mockery::mock(InputInternal::class);
		$input_2->shouldReceive("withInput")->once()->with($input_data)->andReturn($input_2);

		$input_2->shouldReceive("getContent")->once()->andReturn($df->ok(0));

		$filter = new ConcreteFilter(new SignalGenerator(), $this->buildInputFactory(),
			"#", "#", "#", "#",
			"#", "#", [], [], false, false);
		$filter->setInputs([$input_1, $input_2]);
		$filter->input_data = $input_data;

		$filter2 = $filter->withRequest($request);

		$this->assertNotSame($filter2, $filter);
		$this->assertInstanceOf(Filter::class, $filter2);
		$this->assertEquals([$input_1, $input_2], $filter2->getInputs());
	}

	public function test_getData() {
		$df = $this->buildDataFactory();
		$request = \Mockery::mock(ServerRequestInterface::class);
		$request->shouldReceive("getQueryParams")->once()->andReturn([]);

		$input_1 = \Mockery::mock(InputInternal::class);
		$input_1->shouldReceive("getContent")->once()->andReturn($df->ok(1));

		$input_1->shouldReceive("withInput")->once()->andReturn($input_1);

		$input_2 = \Mockery::mock(InputInternal::class);
		$input_2->shouldReceive("getContent")->once()->andReturn($df->ok(2));

		$input_2->shouldReceive("withInput")->once()->andReturn($input_2);

		$filter = new ConcreteFilter(new SignalGenerator(), $this->buildInputFactory(),
			"#", "#", "#", "#",
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
