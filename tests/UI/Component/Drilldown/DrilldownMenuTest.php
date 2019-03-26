<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Implementation\Component\Drilldown;
use \ILIAS\UI\Implementation\Component as I;

/**
 * Tests for the Drilldown.
 */
class DrilldownMenuTest extends ILIAS_UI_TestBase
{
	public function getUIFactory() {
		$factory = new class extends NoUIFactory {
			public function drilldown() {
				return new Drilldown\Factory();
			}
			public function button() {
				return new I\Button\Factory();
			}
		};
		return $factory;
	}

	public function setUp()
	{
		$icon_factory = new I\Icon\Factory();
		$glyph_factory = new I\Glyph\Factory();
		$button_factory = new I\Button\Factory();
		$this->icon = $icon_factory->standard('', '');
		$this->glyph = $glyph_factory->user('');
		$this->button = $button_factory->standard('','');
	}

	public function testConstruction()
	{
		$f = $this->getUIFactory();
		$menu = $f->drilldown()->menu('root');
		$this->assertInstanceOf(
			"ILIAS\\UI\\Component\\Drilldown\\Menu",
			$menu
		);

		return $menu;
	}

	/**
	 * @depends testConstruction
	 */
	public function testGetLabel($menu)
	{
		$this->assertEquals(
			'root',
			$menu->getLabel()
		);
	}

	public function testConstructionWithIcon()
	{
		$f = $this->getUIFactory();
		$menu = $f->drilldown()->menu('root', $this->icon);
		$this->assertEquals(
			$this->icon,
			$menu->getIconOrGlyph()
		);
		return $menu;
	}

	public function testConstructionWithGlyph()
	{
		$f = $this->getUIFactory();
		$menu = $f->drilldown()->menu('root', $this->glyph);
		$this->assertEquals(
			$this->glyph,
			$menu->getIconOrGlyph()
		);
		return $menu;
	}

	public function testWrongConstructionWithButton()
	{
		$this->expectException(\InvalidArgumentException::class);
		$f = $this->getUIFactory();
		$menu = $f->drilldown()->menu('root', $this->button);
	}

	/**
	 * @depends testConstruction
	 */
	public function testRootEntry($menu)
	{
		$f = $this->getUIFactory();
		$menu1 = $f->drilldown()->submenu('root');
		$this->assertEquals(
			array($menu1),
			$menu->getEntries()
		);
	}

	/**
	 * @depends testConstruction
	 */
	public function testWithEntries($menu)
	{
		$f = $this->getUIFactory();
		$root = $f->drilldown()->submenu('root');
		$menu1 = $f->drilldown()->submenu('1');
		$entries = [$menu1, $this->button];
		$expected = [$root->withEntries([$menu1, $this->button])];

		$menu = $menu->withEntries($entries);
		$this->assertEquals(
			$expected,
			$menu->getEntries()
		);
		return $menu;
	}

	/**
	 * @depends testConstruction
	 */
	public function testWithAdditionalEntry($menu)
	{
		$f = $this->getUIFactory();
		$root = $f->drilldown()->submenu('root');
		$root1 = $f->drilldown()->submenu('root1');
		$root2 = $f->drilldown()->submenu('root2');

		$menu = $menu
			->withAdditionalEntry($root1)
			->withAdditionalEntry($root2);

		$expected = array(
			$root->withEntries([$root1, $root2])
		);

		$this->assertEquals(
			$expected,
			$menu->getEntries()
		);
	}

	/**
	 * @depends testConstruction
	 */
	public function testWithWrongAdditionalEntry($menu)
	{
		$this->expectException(\InvalidArgumentException::class);
		$menu = $menu->withAdditionalEntry($this->glyph);
	}

	/**
	 * @depends testConstruction
	 */
	public function testWithWrongEntries($menu)
	{
		$this->expectException(\InvalidArgumentException::class);
		$entries = [$this->button, $this->glyph];
		$menu = $menu->withEntries($entries);
	}


	public function brutallyTrimHTML($html)
	{
		$html = str_replace(["\n", "\t"], "", $html);
		$html = preg_replace('# {2,}#', " ", $html);
		return trim($html);
	}

	/**
	 * @depends testWithEntries
	 */
	public function testRendering($menu)
	{
		$r = $this->getDefaultRenderer();
		$html = $r->render($menu);
		$expected = <<<EOT
			<div class="il-drilldown" id="id_3">
				<ul class="il-drilldown-structure">
					<li class="il-drilldown-entry" id="id_2" data-active="false">
						<span class="entry">
							<button class="btn btn-link" data-action="" >root</button>
						</span>

						<ul class="il-drilldown-level">
							<li class="il-drilldown-entry">
								<li class="il-drilldown-entry" id="id_1" data-active="false">
									<span class="entry">
										<button class="btn btn-link" data-action="" >1</button>
									</span>

									<ul class="il-drilldown-level"></ul>
								</li>
							</li>
							<li class="il-drilldown-entry">
								<button class="btn btn-default" data-action=""></button>
							</li>
						</ul>
					</li>
				</ul>

				<ul class="il-drilldown-backlink"></ul>

				<ul class="il-drilldown-current"></ul>

				<ul class="il-drilldown-visible"></ul>
			</div>
EOT;

		$this->assertEquals(
			$this->brutallyTrimHTML($expected),
			$this->brutallyTrimHTML($html)
		);
	}

}
