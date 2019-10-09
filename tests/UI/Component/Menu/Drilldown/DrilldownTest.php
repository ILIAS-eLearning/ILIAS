<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use \ILIAS\UI\Implementation\Component\Menu;
use \ILIAS\UI\Implementation\Component as I;
use \ILIAS\UI\Component as C;

/**
 * Tests for the Drilldown.
 */
class DrilldownTest extends ILIAS_UI_TestBase
{
    public function getUIFactory()
    {
        $factory = new class extends NoUIFactory {
            public function menu() : C\Menu\Factory
            {
                return new Menu\Factory();
            }
            public function button()
            {
                return new I\Button\Factory();
            }
        };
        return $factory;
    }

    public function setUp() : void
    {
        $icon_factory = new I\Symbol\Icon\Factory();
        $glyph_factory = new I\Symbol\Glyph\Factory();
        $button_factory = new I\Button\Factory();
        $divider_factory = new I\Divider\Factory();
        $this->icon = $icon_factory->standard('', '');
        $this->glyph = $glyph_factory->user('');
        $this->button = $button_factory->standard('', '');
        $this->divider = $divider_factory->horizontal();
        $this->legacy = new I\Legacy\Legacy('');
    }

    public function testConstruction()
    {
        $f = $this->getUIFactory();
        $menu = $f->menu()->drilldown('root', []);
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\Menu\\Menu",
            $menu
        );
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\Menu\\LabeledMenu",
            $menu
        );
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\Menu\\Drilldown",
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

    /**
     * @depends testConstruction
     */
    public function testWithLabel($menu)
    {
        $this->assertEquals(
            'new label',
            $menu->withLabel('new label')->getLabel()
        );
    }

    /**
     * @depends testConstruction
     */
    public function testWithClickableLabel($menu)
    {
        $this->assertEquals(
            $this->button,
            $menu->withLabel($this->button)->getLabel()
        );
    }

    /**
     * @depends testConstruction
     */
    public function testGetItems($menu)
    {
        $this->assertEquals(
            [],
            $menu->getItems()
        );
    }

    public function testWithEntries()
    {
        $f = $this->getUIFactory();
        $items = array(
            $f->menu()->sub('sub', [
                $this->button,
                $this->glyph
            ]),
            $this->divider,
            $this->button
        );
        $menu = $f->menu()->drilldown('root', $items);
        $this->assertEquals(
            $items,
            $menu->getItems()
        );
        return $menu;
    }

    public function testWithWrongLabel()
    {
        $this->expectException(\InvalidArgumentException::class);
        $f = $this->getUIFactory();
        $menu = $f->menu()->drilldown($this->divider, []);
    }

    public function testWithWrongEntry()
    {
        $this->expectException(\InvalidArgumentException::class);
        $f = $this->getUIFactory();
        $menu = $f->menu()->drilldown('label', [$this->legacy]);
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
					<li class="il-menu-item" id="id_1">
						<span class="il-menu-item-label">
							<button class="btn btn-link" data-action="">root</button>
						</span>

						<ul class="il-menu-level">
							<li class="il-menu-item" id="id_2">
								<span class="il-menu-item-label">
									<button class="btn btn-link" data-action="">sub</button>
								</span>

								<ul class="il-menu-level">
									<li class="il-menu-item" id="">
										<span class="il-menu-item-label">
											<button class="btn btn-default" data-action=""></button>
										</span>
									</li>
								</ul>

								<ul class="il-menu-level">
									<li class="il-menu-item" id="">
										<span class="il-menu-item-label">
											<a class="glyph" href="" aria-label="show_who_is_online"><span class="glyphicon glyphicon-user" aria-hidden="true"></span></a>
										</span>
									</li>
								</ul>
							</li>
						</ul>
						<ul class="il-menu-level">
							<li class="il-menu-item" id="">
								<span class="il-menu-item-label">
									<hr />
								</span>
							</li>
						</ul>

						<ul class="il-menu-level">
							<li class="il-menu-item" id="">
								<span class="il-menu-item-label">
									<button class="btn btn-default" data-action=""></button>
								</span>
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
