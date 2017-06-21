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
		return new \ILIAS\UI\Implementation\Component\Breadcrumbs\Factory();
	}

	public function test_implements_factory_interface() {
		$f = $this->getFactory();
		$this->assertInstanceOf("ILIAS\\UI\\Component\\Breadcrumbs\\Factory", $f);

		$c = $f->crumb('label', '#');
		$this->assertInstanceOf("ILIAS\\UI\\Component\\Breadcrumbs\\Crumb", $c);

		$b = $f->bar(array($c, $c));
		$this->assertInstanceOf("ILIAS\\UI\\Component\\Breadcrumbs\\Bar", $b);
	}

	public function test_crumb_attributes() {
		$f = $this->getFactory();
		$c = $f->crumb('label', '#');

		$this->assertEquals('label', $c->label());
		$this->assertEquals('#', $c->url());
	}

	public function test_bar_entries() {
		$f = $this->getFactory();
		$c1 = $f->crumb('label1', '#');
		$c2 = $f->crumb('label2', '#');
		$crumbs = array($c1, $c2);

		$b = $f->bar($crumbs);
		$this->assertEquals($crumbs, $b->crumbs());

		$c3 = $f->crumb('label3', '#');
		array_push($crumbs, $c3);
		$b = $b->withAppendedEntry($c3);
		$this->assertEquals($crumbs, $b->crumbs());

	}

	public function test_bar_appending() {
		$f = $this->getFactory();
		$c1 = $f->crumb('label1', '#');
		$c2 = $f->crumb('label2', '#');
		$c3 = $f->crumb('label2', '#');
		$crumbs = array($c1, $c2);

		$b = $f->bar($crumbs);

		$this->assertEquals($crumbs, $b->crumbs());
	}


}
