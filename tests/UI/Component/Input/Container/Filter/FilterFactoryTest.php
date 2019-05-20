<?php

require_once 'tests/UI/AbstractFactoryTest.php';

use \ILIAS\UI\Component\Input\Container\Filter;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use \ILIAS\Data;
use \ILIAS\Refinery\Validation;
use \ILIAS\Refinery\Transformation;

class FilterFactoryTest extends AbstractFactoryTest {

	public $kitchensink_info_settings = array(
		"standard" => array(
			"context" => false,
		),
	);
	public $factory_title = 'ILIAS\\UI\\Component\\Input\\Container\\Filter\\Factory';


	final public function buildFactory() {
		$df = new Data\Factory();
		$language = $this->createMock(\ilLanguage::class);
		return new \ILIAS\UI\Implementation\Component\Input\Container\Filter\Factory(
			new SignalGenerator(),
			new \ILIAS\UI\Implementation\Component\Input\Field\Factory(
				new SignalGenerator(),
				$df,
				new Validation\Factory($df, $language),
				new Transformation\Factory(),
				new ILIAS\Refinery\Factory($df, $language)
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
