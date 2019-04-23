<?php

require_once 'tests/UI/AbstractFactoryTest.php';

use \ILIAS\UI\Component\Input\Container\Filter;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use \ILIAS\Data;
use \ILIAS\Validation;
use \ILIAS\Transformation;

class FilterFactoryTest extends AbstractFactoryTest {

	public $kitchensink_info_settings = array(
		"standard" => array(
			"context" => false,
		),
	);
	public $factory_title = 'ILIAS\\UI\\Component\\Input\\Container\\Filter\\Factory';


	final public function buildFactory() {
		$df = new Data\Factory();
		return new \ILIAS\UI\Implementation\Component\Input\Container\Filter\Factory(
			new SignalGenerator(),
			new \ILIAS\UI\Implementation\Component\Input\Field\Factory(
				new SignalGenerator(),
				$df,
				new Validation\Factory($df, $this->createMock(\ilLanguage::class)),
				new Transformation\Factory()
			)
		);
	}

	public function test_implements_factory_interface() {
		$f = $this->buildFactory();

		$filter = $f->standard("#", "#", "#", "#",
			"#", "#", [], []);
		$this->assertInstanceOf(Filter\Filter::class, $filter);
		$this->assertInstanceOf(Filter\Standard::class, $filter);
	}
}
