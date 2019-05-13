<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation\Component as IC;
use ILIAS\UI\Implementation\Component\SignalGenerator;

/**
 * Test on Pagination view control.
 */
class PaginationTest extends ILIAS_UI_TestBase {

	public function getUIFactory() {
		$factory = new class extends NoUIFactory
		{
			public function symbol(): C\Symbol\Factory {
				return new IC\Symbol\Factory(
					new IC\Symbol\Icon\Factory(),
					new IC\Symbol\Glyph\Factory()
				);
			}
			public function button() {
				return new IC\Button\Factory(new SignalGenerator());
			}
			public function dropdown() {
				return new IC\Dropdown\Factory();
			}
		};
		return $factory;
	}

	private function getFactory() {
        $sg = new SignalGenerator();
		return new \ILIAS\UI\Implementation\Component\ViewControl\Factory($sg);
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
	<span class="browse previous">
		<a class="glyph" href="?pagination_offset=0" aria-label="back">
			<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
		</a>
	</span>

	<button class="btn btn-link ilSubmitInactive disabled" data-action="?pagination_offset=0">1</button>
	<button class="btn btn-link" data-action="?pagination_offset=1" id="id_1">2</button>

	<span class="browse next">
		<a class="glyph" href="?pagination_offset=1" aria-label="next">
			<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
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
	<span class="browse previous">
		<a class="glyph" href="?pagination_offset=0" aria-label="back">
			<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
		</a>
	</span>

	<button class="btn btn-link" data-action="?pagination_offset=0" id="id_1">1</button>
	<button class="btn btn-link ilSubmitInactive disabled" data-action="?pagination_offset=1">2</button>

	<span class="browse next">
		<a class="glyph" href="?pagination_offset=2" aria-label="next">
			<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
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
	<span class="browse previous">
		<a class="glyph" href="?pagination_offset=0" aria-label="back">
			<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
		</a>
	</span>

	<button class="btn btn-link ilSubmitInactive disabled" data-action="?pagination_offset=0">1</button>

	<span class="last">
		<button class="btn btn-link" data-action="?pagination_offset=2" id="id_1">3</button>
	</span>

	<span class="browse next">
		<a class="glyph" href="?pagination_offset=1" aria-label="next">
			<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
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
	<span class="browse previous">
		<a class="glyph" href="?pagination_offset=0" aria-label="back">
			<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
		</a>
	</span>

	<span class="first">
		<button class="btn btn-link" data-action="?pagination_offset=0" id="id_1">1</button>
	</span>

	<button class="btn btn-link ilSubmitInactive disabled" data-action="?pagination_offset=1">2</button>

	<span class="last">
		<button class="btn btn-link" data-action="?pagination_offset=2" id="id_2">3</button>
	</span>

	<span class="browse next">
		<a class="glyph" href="?pagination_offset=2" aria-label="next">
			<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
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
	<span class="browse previous">
		<a class="glyph" href="?pagination_offset=1" aria-label="back">
			<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
		</a>
	</span>
	<span class="first">
		<button class="btn btn-link" data-action="?pagination_offset=0" id="id_1">1</button>
	</span>

	<button class="btn btn-link ilSubmitInactive disabled" data-action="?pagination_offset=2">3</button>

	<span class="browse next">
		<a class="glyph" href="?pagination_offset=3" aria-label="next">
			<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
		</a>
	</span>
</div>
EOT;
		$html = $this->getDefaultRenderer()->render($p);
		$this->assertHTMLEquals($expected_html, $html);
	}



	public function testRenderDropdown() {
		$p = $this->getFactory()->pagination()
			->withTotalEntries(3)
			->withPageSize(1)
			->withDropdownAt(1);

		$expected_html = <<<EOT
<div class="il-viewcontrol-pagination">
	<span class="browse previous">
		<a class="glyph" href="?pagination_offset=0" aria-label="back">
			<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
		</a>
	</span>

	<div class="dropdown">
		<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">pagination_label_x_of_y <span class="caret"></span></button>
		<ul class="dropdown-menu">
			<li><button class="btn btn-link ilSubmitInactive disabled" data-action="?pagination_offset=0">1</button></li>
			<li><button class="btn btn-link" data-action="?pagination_offset=1" id="id_1">2</button></li>
			<li><button class="btn btn-link" data-action="?pagination_offset=2" id="id_2">3</button></li>
		</ul>
	</div>

	<span class="browse next">
		<a class="glyph" href="?pagination_offset=1" aria-label="next">
			<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
		</a>
	</span>
</div>
EOT;
		$html = $this->getDefaultRenderer()->render($p);
		$this->assertHTMLEquals($expected_html, $html);
	}
}
