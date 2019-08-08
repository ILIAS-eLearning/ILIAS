<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation\Component as I;

/**
 * Tests for the Footer.
 */
class FooterTest extends ILIAS_UI_TestBase
{

	public function setUp(): void
	{
		$f = new I\Link\Factory();
		$this->links = [
			$f->standard("Goto ILIAS", "http://www.ilias.de"),
			$f->standard("go up", "#")
		];
		$this->text = 'footer text';
	}

	protected function getFactory()
	{
		$sig_gen = 	new I\SignalGenerator();
		$sig_gen = 	new I\SignalGenerator();
		$counter_factory = new I\Counter\Factory();
		$slate_factory = new I\MainControls\Slate\Factory($sig_gen, $counter_factory);
		$factory = new I\MainControls\Factory($sig_gen, $slate_factory);
		return $factory;
	}

	public function testConstruction()
	{
		$footer = $this->getFactory()->footer($this->links, $this->text);
		$this->assertInstanceOf(
			"ILIAS\\UI\\Component\\MainControls\\Footer",
			$footer
		);
		return $footer;
	}

	/**
	 * @depends testConstruction
	 */
	public function testGetLinks($footer)
	{
		$this->assertEquals(
			$this->links,
			$footer->getLinks()
		);
	}

	/**
	 * @depends testConstruction
	 */
	public function testGetText($footer)
	{
		$this->assertEquals(
			$this->text,
			$footer->getText()
		);
	}


	protected function brutallyTrimHTML($html)
	{
		$html = str_replace(["\n", "\r", "\t"], "", $html);
		$html = preg_replace('# {2,}#', " ", $html);
		return trim($html);
	}

	public function getUIFactory() {
		$factory = new class extends NoUIFactory {
			public function listing() {
				return new I\Listing\Factory();
			}
		};
		return $factory;
	}


	/**
	 * @depends testConstruction
	 */
	public function testRendering($footer)
	{
		$r = $this->getDefaultRenderer();
		$html = $r->render($footer);

		$expected = <<<EOT
		<div class="il-maincontrols-footer">
			<div class="il-footer-content">
				<div class="il-footer-text">
					footer text
				</div>

				<div class="il-footer-links">
					<ul>
						<li><a href="http://www.ilias.de" >Goto ILIAS</a></li>
						<li><a href="#" >go up</a></li>
					</ul>
				</div>
			</div>
		</div>
EOT;

		$this->assertEquals(
			$this->brutallyTrimHTML($expected),
			$this->brutallyTrimHTML($html)
		);
	}

}
