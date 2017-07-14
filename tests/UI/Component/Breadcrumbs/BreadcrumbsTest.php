<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Component as C;

/**
 * Tests for the Breadcrumbs-component
 */
class BreadcrumbsTest extends ILIAS_UI_TestBase {
	public function getFactory() {
		return new \ILIAS\UI\Implementation\Factory();
	}

	public function test_implements_factory_interface() {
		$f = $this->getFactory();
		$c = $f->Breadcrumbs(array());

		$this->assertInstanceOf("ILIAS\\UI\\Factory", $f);
		$this->assertInstanceOf(
			"ILIAS\\UI\\Component\\Breadcrumbs\\Breadcrumbs",
			$f->Breadcrumbs(array())
		);
	}

	public function testCrumbs() {
		$f = $this->getFactory();
		$crumbs = array(
			$f->link()->standard("label", '#'),
			$f->link()->standard("label2", '#')
		);

		$c = $f->Breadcrumbs($crumbs);
		$this->assertEquals($crumbs, $c->getItems());
	}

	public function testAppending() {
		$f = $this->getFactory();
		$crumb  = $f->link()->standard("label", '#');

		$c = $f->Breadcrumbs(array())
			->withAppendedItem($crumb);
		$this->assertEquals(array($crumb), $c->getItems());
	}

	public function testRendering() {
		$f = $this->getFactory();
		$r = $this->getDefaultRenderer();

		$crumbs = array(
			$f->link()->standard("label", '#'),
			$f->link()->standard("label2", '#')
		);
		$c = $f->Breadcrumbs($crumbs);

		$html = $this->normalizeHTML($r->render($c));
		$expected = '<nav role="navigation" aria-label="breadcrumbs">'
			.'	<ul class="breadcrumb">'
			.'		<li class="crumb">'
			.'			<a href="#">label</a>'
			.'		</li>'
			.'		<li class="crumb">'
			.'			<a href="#">label2</a>'
			.'		</li>'
			.'	</ul>'
			.'</nav>';

		$this->assertHTMLEquals($expected, $html);
	}
}
