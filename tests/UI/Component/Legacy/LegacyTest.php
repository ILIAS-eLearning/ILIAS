<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation\Component as IC;

/**
 * Test on button implementation.
 */
class LegacyTest extends ILIAS_UI_TestBase {

	public function getUIFactory() {
		$factory = new class extends NoUIFactory {
			public function legacy($content) {
				return new IC\Legacy\Legacy($content);
			}
		};
		return $factory;
	}

	public function test_implements_factory_interface() {
		$f = $this->getUIFactory();

		$this->assertInstanceOf("ILIAS\\UI\\Factory", $f);
		$this->assertInstanceOf
		( "ILIAS\\UI\\Component\\Legacy\\Legacy"
				, $f->legacy("Legacy Content")
		);
	}

	public function test_get_content() {
		$f = $this->getUIFactory();
		$g = $f->legacy("Legacy Content");

		$this->assertEquals($g->getContent(), "Legacy Content");
	}


	public function test_render_content() {
		$f = $this->getUIFactory();
		$r = $this->getDefaultRenderer();

		$g = $f->legacy("Legacy Content");

		$this->assertEquals($r->render($g), "Legacy Content");
	}
}
