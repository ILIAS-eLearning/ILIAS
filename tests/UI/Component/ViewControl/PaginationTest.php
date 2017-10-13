<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\SignalGenerator;

/**
 * Test on Pagination view control.
 */
class PaginationTest extends ILIAS_UI_TestBase {

	private function getFactory() {
		$f = new \ILIAS\UI\Implementation\Factory();
		return $f->viewControl();
	}

	public function testConstruction() {
		$f = $this->getFactory();
		$pagination = $f->pagination();
		$this->assertInstanceOf(
			"ILIAS\\UI\\Component\\ViewControl\\Pagination",
			$pagination
		);
		$this->assertInstanceOf(
			"ILIAS\\UI\\Component\\Signal",
			$pagination->getSelectSignal()
		);
	}

	public function testAttributes() {

		$total_entries = 111;
		$page_size = 100;
		$current_page = 1;
		//$select_signal;
		$target_url = 'http://testurl';
		$paramter_name = "param_name";
		$max_page_options = 10;

		$f = $this->getFactory();
		$p = $f->pagination()
			->withTargetURL($target_url, $paramter_name)
			->withTotalEntries($total_entries)
			->withPageSize($page_size)
			->withCurrentPage($current_page)
			->withMaxPaginationButtons($max_page_options)
			;

		$this->assertEquals($target_url, $p->getTargetURL());
		$this->assertEquals($paramter_name, $p->getParameterName());
		$this->assertEquals($page_size, $p->getPageSize());
		$this->assertEquals($current_page, $p->getCurrentPage());
		$this->assertEquals($max_page_options, $p->getMaxPaginationButtons());
		$this->assertEquals(2, $p->getNumberOfPages());
		$this->assertEquals(11, $p->getPageLength());
	}
}
