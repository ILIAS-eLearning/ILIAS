<?php

/* Copyright (c) 2017 Alexander Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Component as C;


/**
 * Test on card implementation.
 */
class DropdownTest extends ILIAS_UI_TestBase {

	/**
	 * @return \ILIAS\UI\Implementation\Factory
	 */
	public function getFactory() {
		return new \ILIAS\UI\Implementation\Factory();
	}

	public function test_implements_factory_interface() {
		$f = $this->getFactory();

		$this->assertInstanceOf( "ILIAS\\UI\\Component\\Dropdown\\Standard", $f->dropdown()->standard(array()));
	}

	public function test_with_label() {
		$f = $this->getFactory();

		$c = $f->dropdown()->standard(array())->withLabel("label");

		$this->assertEquals($c->getLabel(), "label");
	}

	public function test_with_items() {
		$f = $this->getFactory();
		$c = $f->dropdown()->standard(array(
			$f->button()->shy("ILIAS", "https://www.ilias.de"),
			$f->button()->shy("GitHub", "https://www.github.com"),
			$f->divider()->horizontal(),
			$f->button()->shy("GitHub", "https://www.github.com")
		));
		$items = $c->getItems();

		$this->assertTrue(is_array($items));
		$this->assertInstanceOf("ILIAS\\UI\\Component\\Button\\Shy", $items[0]);
		$this->assertInstanceOf("ILIAS\\UI\\Component\\Divider\\Horizontal", $items[2]);
	}

	public function test_render_empty() {
		$f = $this->getFactory();
		$r = $this->getDefaultRenderer();

		$c = $f->dropdown()->standard(array());

		$html = $r->render($c);
		$expected = "";

		$this->assertEquals($expected, $html);
	}

	public function test_render_items() {
		$f = $this->getFactory();
		$r = $this->getDefaultRenderer();

		$c = $f->dropdown()->standard(array(
			$f->button()->shy("ILIAS", "https://www.ilias.de"),
			$f->divider()->horizontal(),
			$f->button()->shy("GitHub", "https://www.github.com")
		));

		$html = $r->render($c);

		$expected = <<<EOT
			<div class="dropdown">
				<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu">
					<li><a class="btn btn-link" href="https://www.ilias.de" data-action="https://www.ilias.de">ILIAS</a></li>
					<li><hr  /></li>
					<li><a class="btn btn-link" href="https://www.github.com" data-action="https://www.github.com">GitHub</a></li>
				</ul>
			</div>
EOT;

		$this->assertHTMLEquals($expected, $html);
	}

	public function test_render_items_with_label() {
		$f = $this->getFactory();
		$r = $this->getDefaultRenderer();

		$c = $f->dropdown()->standard(array(
			$f->button()->shy("ILIAS", "https://www.ilias.de"),
			$f->divider()->horizontal(),
			$f->button()->shy("GitHub", "https://www.github.com")
		))->withLabel("label");

		$html = $r->render($c);

		$expected = <<<EOT
			<div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"  aria-haspopup="true" aria-expanded="false">label <span class="caret"></span></button>
				<ul class="dropdown-menu">
					<li><a class="btn btn-link" href="https://www.ilias.de" data-action="https://www.ilias.de">ILIAS</a></li>
					<li><hr  /></li>
					<li><a class="btn btn-link" href="https://www.github.com" data-action="https://www.github.com">GitHub</a></li>
				</ul>
			</div>
EOT;

		$this->assertHTMLEquals($expected, $html);
	}
}
