<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../../Base.php");

use ILIAS\UI\Implementation\Component\Input;
use \ILIAS\UI\Implementation\Component\Input\Field\InputInternal;
use \ILIAS\UI\Implementation\Component\Input\NameSource;
use \ILIAS\UI\Implementation\Component\Input\Container\Filter\Filter;
use ILIAS\UI\Implementation\Component\SignalGenerator;

class FixedNameSourceFilter implements NameSource {

	public $name = "name";


	public function getNewName() {
		return $this->name;
	}
}


class ConcreteFilter extends Filter {
	/*
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
	*/
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


	public function test_getInputs() {
		//$f = $this->buildFactory();
		$if = $this->buildInputFactory();
		$name_source = new FixedNameSourceFilter();

		$inputs = [$if->text(""), $if->text("")];
		$inputs_rendered = [true, true];
		$filter = new ConcreteFilter("#", "#", "#", "#",
			"#", "#", $inputs, $inputs_rendered, false, false);

		$seen_names = [];
		$inputs = $filter->getInputs();
		$this->assertEquals(count($inputs), count($inputs));

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
