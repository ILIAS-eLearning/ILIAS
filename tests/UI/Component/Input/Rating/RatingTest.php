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
/*
	static $canonical_css_classes = array
		( "standard"	=>	 "btn btn-default"
		, "primary"	 =>	 "btn btn-default btn-primary"
		);
*/

	public function test_implements_factory_interface() {
		$f = $this->getInputFactory();
		$this->assertInstanceOf("ILIAS\\UI\\Component\\Input\\Factory", $f);

		$r = $f->rating('topic');
		$this->assertInstanceOf("ILIAS\\UI\\Component\\Input\\Rating\\Rating", $r);
	}


	public function test_with_topic() {
		$r = $this->getInputFactory()->rating('topic');
		$this->assertEquals('topic', $r->topic());
		$r = $r->withTopic('another topic');
		$this->assertEquals('another topic', $r->topic());
	}

	public function test_with_byline() {
		$r = $this->getInputFactory()->rating('topic', 'my byline');
		$this->assertEquals('my byline', $r->byline());
		$r = $r->withByline('another byline');
		$this->assertEquals('another byline', $r->byline());
	}

	public function test_with_scale() {
		$r = $this->getInputFactory()->rating('topic');
		$expected = array_fill(0, 5, '');
		$this->assertEquals($expected, $r->captions());

		$expected = array(
				 'opt1'
				,'opt2'
				,'opt3'
				,'opt4'
				,'opt5'
			);
		$r = $r->withCaptions($expected);
		$this->assertEquals($expected, $r->captions());
	}


	public function test_render() {
		$renderer = $this->getDefaultRenderer();
		$r = $this->getInputFactory()->rating('topic');
		$html = $renderer->render($r);

	}


}
