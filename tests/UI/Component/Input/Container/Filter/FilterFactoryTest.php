<?php

require_once 'tests/UI/AbstractFactoryTest.php';

use \ILIAS\UI\Component\Input\Container\Filter;

class FilterFactoryTest extends AbstractFactoryTest {

	public $kitchensink_info_settings = array(
		"standard" => array(
			"context" => false,
		),
	);
	public $factory_title = 'ILIAS\\UI\\Component\\Input\\Container\\Filter\\Factory';


	final public function buildFactory() {
		return new \ILIAS\UI\Implementation\Component\Input\Container\Filter\Factory;
	}

	public function test_implements_factory_interface() {
		$f = $this->buildFactory();

		$filter = $f->standard("#", "#", "#", "#",
			"#", "#", [], []);
		$this->assertInstanceOf(Filter\Filter::class, $filter);
		$this->assertInstanceOf(Filter\Standard::class, $filter);
	}
}
