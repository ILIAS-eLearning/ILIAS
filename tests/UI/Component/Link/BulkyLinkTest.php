<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Component\Link as C;
use \ILIAS\UI\Implementation\Component as I;
/**
 * Testing behavior of the Bulky Link.
 */
class BulkyLinkTest extends ILIAS_UI_TestBase
{
	public function setUp(): void{
		$this->factory = new I\Link\Factory();
		$this->glyph = new I\Symbol\Glyph\Glyph("briefcase", "briefcase");
		$this->icon = new I\Symbol\Icon\Standard("someExample","Example", "small", false);
	}

	public function testImplementsInterfaces()
	{
		$link = $this->factory->bulky($this->glyph, "label", "http://www.ilias.de");
		$this->assertInstanceOf(C\Bulky::class, $link);
		$this->assertInstanceOf(C\Link::class, $link);
	}

	public function testWrongConstruction()
	{
		$this->expectException(\TypeError::class);
		$link = $this->factory->bulky('wrong param', "label", "http://www.ilias.de");
	}

	public function testGetLabell()
	{
		$label = 'some label for the link';
		$link = $this->factory->bulky($this->glyph, $label, "http://www.ilias.de");
		$this->assertEquals($label, $link->getLabel());
	}

	public function testGetGlyphSymbol()
	{
		$link = $this->factory->bulky($this->glyph, "label", "http://www.ilias.de");
		$this->assertEquals($this->glyph, $link->getSymbol());
		$link = $this->factory->bulky($this->icon, "label", "http://www.ilias.de");
		$this->assertEquals($this->icon, $link->getSymbol());
	}

	public function testRenderingGlyph() {
		$r = $this->getDefaultRenderer();
		$b = $this->factory->bulky($this->glyph, "label", "http://www.ilias.de");

		$expected = ''
			.'<a class="il-link link-bulky" href="http://www.ilias.de">'
			.'	<span class="glyph" aria-label="briefcase">'
			.'		<span class="glyphicon glyphicon-briefcase" aria-hidden="true"></span>'
			.'	</span>'
			.'	<span class="bulky-label">label</span>'
			.'</a>';

		$this->assertHTMLEquals(
			$expected,
			$r->render($b)
		);
	}

	public function testRenderingIcon() {
		$r = $this->getDefaultRenderer();
		$b = $this->factory->bulky($this->icon, "label", "http://www.ilias.de");

		$expected = ''
			.'<a class="il-link link-bulky" href="http://www.ilias.de">'
			.'	<div class="icon someExample small" aria-label="Example"></div>'
			.'	<span class="bulky-label">label</span>'
			.'</a>';

		$this->assertHTMLEquals(
			$expected,
			$r->render($b)
		);
	}




}
