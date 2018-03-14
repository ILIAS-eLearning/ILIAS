<?php
/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Component as C;

/**
 * Tests for Presentation Table.
 */
class PresentationTest extends ILIAS_UI_TestBase {
	private function getFactory() {
		return new \ILIAS\UI\Implementation\Factory();
	}

	public function testConstruction() {
		$f = $this->getFactory();
		$this->assertInstanceOf("ILIAS\\UI\\Component\\Table\\Factory", $f->table());

		$pt = $f->table()->presentation('title', array(),	function(){});
		$this->assertInstanceOf("ILIAS\\UI\\Component\\Table\\Presentation", $pt);

		$this->assertEquals("title", $pt->getTitle());
		$this->assertEquals(array(), $pt->getViewControls());
		$this->assertInstanceOf(\Closure::class, $pt->getRowMapping());

		$pt = $pt
			->withEnvironment(array('k'=>'v'))
			->withData(array('dk'=>'dv'));
		$this->assertEquals(array('k'=>'v'), $pt->getEnvironment());
		$this->assertEquals(array('dk'=>'dv'), $pt->getData());
	}

}