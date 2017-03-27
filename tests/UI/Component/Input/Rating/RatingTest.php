<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../../Base.php");

use \ILIAS\UI\Component as C;

/**
 * Test on rating implementation.
 */
class RatingTest extends ILIAS_UI_TestBase {
	public function getInputFactory() {
		return new \ILIAS\UI\Implementation\Component\Input\Factory();
	}

	public function test_implements_factory_interface() {
		$f = $this->getInputFactory();
		$this->assertInstanceOf("ILIAS\\UI\\Component\\Input\\Factory", $f);

		$r = $f->rating('topic');
		$this->assertInstanceOf("ILIAS\\UI\\Component\\Input\\Rating\\Rating", $r);
		$this->assertEquals('topic', $r->topic());

		$captions =  array('opt1');
		$r = $f->rating('topic', $captions);
		$expected = array('opt1','','','','');
		$this->assertEquals($expected, $r->captions());

		$captions =  array('opt1', 'opt2', 'opt3', 'opt4', 'opt5', 'opt6');
		$r = $f->rating('topic', $captions);
		$expected = array('opt1', 'opt2', 'opt3', 'opt4', 'opt5');
		$this->assertEquals($expected, $r->captions());

	}

	public function test_with_byline() {
		$r = $this->getInputFactory()->rating('topic');
		$r = $r->withByline('my byline');
		$this->assertEquals('my byline', $r->byline());
	}

	public function test_render() {
		$renderer = $this->getDefaultRenderer();
		$r = $this->getInputFactory()->rating('topic');
		$html = $renderer->render($r);

	}


}
