<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Component as C;

/**
 * Test on button implementation.
 */
class ImageTest extends ILIAS_UI_TestBase {

	/**
	 * @return \ILIAS\UI\Implementation\Component\Image\Factory
	 */
	public function getImageFactory() {
		return new \ILIAS\UI\Implementation\Component\Image\Factory();
	}


	public function test_implements_factory_interface() {
		$f = $this->getImageFactory();

		$this->assertInstanceOf("ILIAS\\UI\\Component\\Image\\Factory", $f);
		$this->assertInstanceOf("ILIAS\\UI\\Component\\Image\\Image", $f->standard("source","alt"));
		$this->assertInstanceOf("ILIAS\\UI\\Component\\Image\\Image", $f->responsive("source","alt"));
	}

	public function test_get_type() {
		$f = $this->getImageFactory();
		$i = $f->standard("source","alt");

		$this->assertEquals($i::STANDARD, $i->getType());
	}

	public function test_get_source() {
		$f = $this->getImageFactory();
		$i = $f->standard("source","alt");

		$this->assertEquals("source", $i->getSource());
	}

	public function test_get_alt() {
		$f = $this->getImageFactory();
		$i = $f->standard("source","alt");

		$this->assertEquals("alt", $i->getAlt());
	}


	public function test_set_source() {
		$f = $this->getImageFactory();
		$i = $f->standard("source","alt");
		$i = $i->withSource("newSource");
		$this->assertEquals("newSource", $i->getSource());
	}

	public function test_set_alt() {
		$f = $this->getImageFactory();
		$i = $f->standard("source","alt");
		$i = $i->withAlt("newAlt");
		$this->assertEquals("newAlt", $i->getAlt());
	}

	public function test_invalid_source(){
		$f = $this->getImageFactory();

		try{
			$f->standard(1,"alt");
			$this->assertFalse("This should not happen");
		}catch(InvalidArgumentException $e){}
	}

	public function test_invalid_alt(){
		$f = $this->getImageFactory();

		try{
			$f->standard("source",1);
			$this->assertFalse("This should not happen");
		}catch(InvalidArgumentException $e){}
	}

	public function test_render_standard() {
		$f = $this->getImageFactory();
		$r = $this->getDefaultRenderer();
		$i = $f->standard("source","alt");

		$html = $this->normalizeHTML($r->render($i));

		$expected = "<img src=\"source\" class=\"img-standard\" alt=\"alt\">";

		$this->assertEquals($expected, $html);
	}

	public function test_render_responsive() {
		$f = $this->getImageFactory();
		$r = $this->getDefaultRenderer();
		$i = $f->responsive("source","alt");

		$html = $this->normalizeHTML($r->render($i));

		$expected = "<img src=\"source\" class=\"img-responsive\" alt=\"alt\">";

		$this->assertEquals($expected, $html);
	}
}
