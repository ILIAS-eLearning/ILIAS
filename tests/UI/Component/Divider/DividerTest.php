<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Component as C;


/**
 * Test on divider implementation.
 */
class DividerTest extends ILIAS_UI_TestBase {

	/**
	 * @return \ILIAS\UI\Implementation\Factory
	 */
	public function getFactory() {
		return new \ILIAS\UI\Implementation\Factory();
	}

	public function test_implements_factory_interface() {
		$f = $this->getFactory();

		$this->assertInstanceOf( "ILIAS\\UI\\Component\\Divider\\Horizontal", $f->divider()->horizontal());
	}

	public function test_with_label() {
		$f = $this->getFactory();
		$c = $f->divider()->horizontal()->withLabel("label");

		$this->assertEquals($c->getLabel(), "label");
	}

	public function test_render_horizontal_empty() {
		$f = $this->getFactory();
		$r = $this->getDefaultRenderer();

		$c = $f->divider()->horizontal();

		$html = trim($r->render($c));

		$expected_html = "<hr/>";

		$this->assertHTMLEquals($expected_html, $html);
	}

	public function test_render_horizontal_with_label() {
		$f = $this->getFactory();
		$r = $this->getDefaultRenderer();

		$c = $f->divider()->horizontal()->withLabel("label");

		$html = trim($r->render($c));
		$expected_html = '<hr class="il-divider-with-label" /><h6 class="il-divider">label</h6>';

		$this->assertHTMLEquals("<div>".$expected_html."</div>", "<div>".$html."</div>");
	}

}
