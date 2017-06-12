<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Component as C;

/**
 * Test on icon implementation.
 */
class IconTest extends ILIAS_UI_TestBase {
	private function getIconFactory() {
		$f = new \ILIAS\UI\Implementation\Factory();
		return $f->icon();
	}

	public function testConstruction() {
		$f = $this->getIconFactory();
		$this->assertInstanceOf("ILIAS\\UI\\Component\\Icon\\Factory", $f);

		$si = $f->standard('course', 'Kurs');
		$this->assertInstanceOf("ILIAS\\UI\\Component\\Icon\\Standard", $si);

		$ci = $f->custom('course', 'Kurs');
		$this->assertInstanceOf("ILIAS\\UI\\Component\\Icon\\Custom", $ci);
	}

	public function testAttributes() {
		$f = $this->getIconFactory();

		$ico = $f->standard('course', 'Kurs');
		$this->assertEquals('Kurs', $ico->getAriaLabel());
		$this->assertEquals('course', $ico->getCSSClass());
		$this->assertEquals('small', $ico->getSize());
		$this->assertNull($ico->getAbbreviation());

		$ico = $ico->withAbbreviation('K');
		$this->assertEquals('K', $ico->getAbbreviation());
	}

	public function testSizeModification() {
		$f = $f = $this->getIconFactory();
		$ico = $f->standard('course', 'Kurs');

		$ico = $ico->withSize('medium');
		$this->assertEquals('medium', $ico->getSize());

		$ico = $ico->withSize('large');
		$this->assertEquals('large', $ico->getSize());

		$ico = $ico->withSize('small');
		$this->assertEquals('small', $ico->getSize());
	}

	public function testSizeModificationWrongParam() {
		$this->setExpectedException(\InvalidArgumentException::class);

		$f = $f = $this->getIconFactory();
		$ico = $f->standard('course', 'Kurs');
		$ico = $ico->withSize('tiny');
	}

	public function testCustomPath() {
		$f = $f = $this->getIconFactory();

		$ico = $f->custom('/some/path/', 'Custom Icon');
		$this->assertEquals('/some/path/', $ico->getIconPath());
	}

}
