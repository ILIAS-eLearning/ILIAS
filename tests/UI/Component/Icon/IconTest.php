<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Component as C;

/**
 * Test on icon implementation.
 */
class IconTest extends ILIAS_UI_TestBase {

	public function getIcon() {
		$f = new \ILIAS\UI\Implementation\Factory();
		return $f->icon('course', 'Kurs', 'large', 'K');
	}


	public function test_attributes() {
		$ico = $this->getIcon();
		$expected = array(
			'course',
			'Kurs',
			'large',
			'K'
		);
		$this->assertEquals(
			$expected,
			array(
				$ico->cssclass(),
				$ico->aria(),
				$ico->size(),
				$ico->abbreviation()
			)
		);
	}

	public function test_size_modification() {
		$ico = $this->getIcon();
		$ico = $ico->withSize('small');
		$this->assertEquals('small', $ico->size());
	}

	public function test_size_modification_wrong_param() {
		$this->setExpectedException(\InvalidArgumentException::class);
		$ico = $this->getIcon();
		$ico = $ico->withSize('tiny');
	}

}
