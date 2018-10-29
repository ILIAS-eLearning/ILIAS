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

	protected function buildFactory()
	{
		return new ILIAS\UI\Implementation\Component\Input\Container\Filter\Factory(new SignalGenerator());
	}


	protected function buildInputFactory()
	{
		return new ILIAS\UI\Implementation\Component\Input\Field\Factory(new SignalGenerator());
	}


	public function test_getInputs()
	{
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
}
