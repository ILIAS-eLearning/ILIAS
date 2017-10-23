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
			$pagination->getInternalSignal()
		);
	}

	public function testAttributes() {
		$total_entries = 111;
		$page_size = 100;
		$current_page = 1;
		//$select_signal;
		$target_url = 'http://testurl';
		$parameter_name = "param_name";
		$max_page_options = 10;

		$f = $this->getFactory();
		$p = $f->pagination()
			->withTargetURL($target_url, $parameter_name)
			->withTotalEntries($total_entries)
			->withPageSize($page_size)
			->withCurrentPage($current_page)
			->withMaxPaginationButtons($max_page_options)
			;

		$this->assertEquals($target_url, $p->getTargetURL());
		$this->assertEquals($parameter_name, $p->getParameterName());
		$this->assertEquals($page_size, $p->getPageSize());
		$this->assertEquals($current_page, $p->getCurrentPage());
		$this->assertEquals($max_page_options, $p->getMaxPaginationButtons());
		$this->assertEquals(2, $p->getNumberOfPages());
		$this->assertEquals(11, $p->getPageLength());
	}

	public function testRenderUnlimited() {
		$p = $this->getFactory()->pagination()
			->withTotalEntries(2)
			->withPageSize(1);

		//two entries, first one inactive
		//rocker left disabled
		$expected_html = <<<EOT
<div class="il-viewcontrol-pagination">
	<span class="rocker previous">
		<a class="btn btn-link ilSubmitInactive disabled" data-action="?pagination_offset=0">
			<span class="glyphicon glyphicon-chevron-left"></span>
		</a>
	</span>

	<a class="btn btn-link ilSubmitInactive disabled" data-action="?pagination_offset=0">1</a>
	<a class="btn btn-link" href="?pagination_offset=1" data-action="?pagination_offset=1">2</a>

	<span class="rocker next">
		<a class="btn btn-link" href="?pagination_offset=1" data-action="?pagination_offset=1">
			<span class="glyphicon glyphicon-chevron-right"></span>
		</a>
	</span>
</div>
EOT;

		$html = $this->getDefaultRenderer()->render($p);
		$this->assertHTMLEquals($expected_html, $html);
	}

	public function testRenderWithCurrentPage() {
		$p = $this->getFactory()->pagination()
			->withTotalEntries(2)
			->withPageSize(1)
			->withCurrentPage(1);

		//two entries, second one inactive
		//rocker right disabled
		$expected_html = <<<EOT
<div class="il-viewcontrol-pagination">
	<span class="rocker previous">
		<a class="btn btn-link" href="?pagination_offset=0" data-action="?pagination_offset=0">
			<span class="glyphicon glyphicon-chevron-left"></span>
		</a>
	</span>

	<a class="btn btn-link" href="?pagination_offset=0" data-action="?pagination_offset=0">1</a>
	<a class="btn btn-link ilSubmitInactive disabled" data-action="?pagination_offset=1">2</a>

	<span class="rocker next">
		<a class="btn btn-link ilSubmitInactive disabled" data-action="?pagination_offset=2">
			<span class="glyphicon glyphicon-chevron-right"></span>
		</a>
	</span>
</div>
EOT;

		$html = $this->getDefaultRenderer()->render($p);
		$this->assertHTMLEquals($expected_html, $html);
	}

	public function testRenderLimited() {
		$p = $this->getFactory()->pagination()
			->withTotalEntries(3)
			->withPageSize(1)
			->withMaxPaginationButtons(1);

		//one entry,
		//rocker left disabled
		//boundary-button right
		$expected_html = <<<EOT
<div class="il-viewcontrol-pagination">
	<span class="rocker previous">
		<a class="btn btn-link ilSubmitInactive disabled" data-action="?pagination_offset=0">
			<span class="glyphicon glyphicon-chevron-left"></span>
		</a>
	</span>

	<a class="btn btn-link ilSubmitInactive disabled" data-action="?pagination_offset=0">1</a>

	<span class="last">
		<a class="btn btn-link" href="?pagination_offset=2" data-action="?pagination_offset=2">3</a>
	</span>

	<span class="rocker next">
		<a class="btn btn-link" href="?pagination_offset=1" data-action="?pagination_offset=1">
			<span class="glyphicon glyphicon-chevron-right"></span>
		</a>
	</span>
</div>
EOT;
		$html = $this->getDefaultRenderer()->render($p);
		$this->assertHTMLEquals($expected_html, $html);
	}

	public function testRenderLimitedWithCurrentPage() {
		$p = $this->getFactory()->pagination()
			->withTotalEntries(3)
			->withPageSize(1)
			->withMaxPaginationButtons(1)
			->withCurrentPage(1);

		//one entry,
		//both rockers enabled
		//both boundary-buttons
		$expected_html = <<<EOT
<div class="il-viewcontrol-pagination">
	<span class="rocker previous">
		<a class="btn btn-link" href="?pagination_offset=0" data-action="?pagination_offset=0">
			<span class="glyphicon glyphicon-chevron-left"></span>
		</a>
	</span>

	<span class="first">
		<a class="btn btn-link" href="?pagination_offset=0" data-action="?pagination_offset=0">1</a>
	</span>

	<a class="btn btn-link ilSubmitInactive disabled" data-action="?pagination_offset=1">2</a>

	<span class="last">
		<a class="btn btn-link" href="?pagination_offset=2" data-action="?pagination_offset=2">3</a>
	</span>

	<span class="rocker next">
		<a class="btn btn-link" href="?pagination_offset=2" data-action="?pagination_offset=2">
			<span class="glyphicon glyphicon-chevron-right"></span>
		</a>
	</span>
</div>
EOT;
		$html = $this->getDefaultRenderer()->render($p);
		$this->assertHTMLEquals($expected_html, $html);
	}

	public function testRenderLimitedWithCurrentPage2() {
		$p = $this->getFactory()->pagination()
			->withTotalEntries(3)
			->withPageSize(1)
			->withMaxPaginationButtons(1)
			->withCurrentPage(2);

		//one entry,
		//rocker right disabled
		//boundary-button left only
		$expected_html = <<<EOT
<div class="il-viewcontrol-pagination">
	<span class="rocker previous">
		<a class="btn btn-link" href="?pagination_offset=1" data-action="?pagination_offset=1">
			<span class="glyphicon glyphicon-chevron-left"></span>
		</a>
	</span>

	<span class="first">
		<a class="btn btn-link" href="?pagination_offset=0" data-action="?pagination_offset=0">1</a>
	</span>

	<a class="btn btn-link ilSubmitInactive disabled" data-action="?pagination_offset=2">3</a>

	<span class="rocker next">
		<a class="btn btn-link ilSubmitInactive disabled" data-action="?pagination_offset=3">
			<span class="glyphicon glyphicon-chevron-right"></span>
		</a>
	</span>
</div>
EOT;
		$html = $this->getDefaultRenderer()->render($p);
		$this->assertHTMLEquals($expected_html, $html);
	}
}
