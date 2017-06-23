<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Component as C;


/**
 * Test month button
 */
class ButtonMonthTest extends ILIAS_UI_TestBase {

	/**
	 * Setup
	 */
	public function setUp()
	{
		// setup stub for global ilPluginAdmin
		include_once("./Services/UICore/classes/class.ilTemplate.php");
		global $tpl;
		$tpl = $this->getMockBuilder('ilTemplate')
			->disableOriginalConstructor()
			->getMock();
		$tpl->method('addJavaScript')
			->willReturn("");
	}

	/**
	 * @return \ILIAS\UI\Implementation\Factory
	 */
	public function getFactory() {
		return new \ILIAS\UI\Implementation\Factory();
	}

	public function test_implements_factory_interface() {
		$f = $this->getFactory();

		$this->assertInstanceOf( "ILIAS\\UI\\Component\\Button\\Month", $f->button()->month("02-2017"));
	}

	public function test_get_default() {
		$f = $this->getFactory();
		$c =  $f->button()->month("02-2017");

		$this->assertEquals($c->getDefault(), "02-2017");
	}

	public function test_render() {
		$f = $this->getFactory();
		$r = $this->getDefaultRenderer();

		$c = $f->button()->month("02-2017");

		$html = $r->render($c);
		$expected_html = <<<EOT
		<div  class="btn-group il-btn-month">
	<button type="button" class="btn btn-default dropdown-toggle" href="" data-toggle="dropdown" aria-expanded="false">
		<span class="il-current-month">month_02_long 2017</span>
		<span class="caret"></span>
		<span class="sr-only"></span>
	</button>
	<div class="dropdown-menu">
		<div class="inline-picker"></div>
	</div>
</div>
EOT;
		$this->assertHTMLEquals($expected_html, $html);
	}

}
