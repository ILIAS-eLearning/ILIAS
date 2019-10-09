<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation\Component as I;
use \ILIAS\UI\Implementation\Component\MainControls\Slate\Slate;
use \ILIAS\UI\Implementation\Component\MainControls\Slate\Combined;
use \ILIAS\UI\Component\Signal;

/**
 * Tests for the Slate.
 */
class CombinedSlateTest extends ILIAS_UI_TestBase
{
    public function setUp() : void
    {
        $this->sig_gen = new I\SignalGenerator();
        $this->button_factory = new I\Button\Factory($this->sig_gen);
        $this->icon_factory = new I\Symbol\Icon\Factory();
    }

    public function getUIFactory()
    {
        $factory = new class extends NoUIFactory {
            public function button()
            {
                return $this->button_factory;
            }
            public function glyph()
            {
                return new I\Symbol\Glyph\Factory();
            }
            public function mainControls() : C\MainControls\Factory
            {
                return new I\MainControls\Factory($this->sig_gen);
            }
        };
        $factory->button_factory = $this->button_factory;
        $factory->sig_gen = $this->sig_gen;
        return $factory;
    }

    public function brutallyTrimHTML($html)
    {
        $html = str_replace(["\n", "\r", "\t"], "", $html);
        $html = preg_replace('# {2,}#', " ", $html);
        return trim($html);
    }

    public function testRendering()
    {
        $name = 'name';
        $icon = $this->icon_factory->custom('', '');
        $slate = new Combined($this->sig_gen, $name, $icon);

        $r = $this->getDefaultRenderer();
        $html = $r->render($slate);

        $expected = '<div class="il-maincontrols-slate disengaged" id="id_1"><div class="il-maincontrols-slate-content" data-replace-marker="content"></div></div>';
        $this->assertEquals(
            $expected,
            $this->brutallyTrimHTML($html)
        );
    }

    public function testRenderingWithSubslateAndButton()
    {
        $name = 'name';
        $icon = $this->icon_factory->custom('', '');
        $subslate = new Combined($this->sig_gen, $name, $icon);
        $subbutton = $this->button_factory->bulky($icon, '', '');
        $slate = new Combined($this->sig_gen, $name, $icon);
        $slate = $slate
            ->withAdditionalEntry($subslate)
            ->withAdditionalEntry($subbutton);

        $r = $this->getDefaultRenderer();
        $html = $r->render($slate);

        $expected = <<<EOT
		<div class="il-maincontrols-slate disengaged" id="id_3">
			<div class="il-maincontrols-slate-content" data-replace-marker="content">

				<button class="btn btn-bulky" id="id_1" >
					<div class="icon custom small" aria-label="">
						<img src="" />
					</div>
					<div>
						<span class="bulky-label">name</span>
					</div>
				</button>
				<div class="il-maincontrols-slate disengaged" id="id_2">
					<div class="il-maincontrols-slate-content" data-replace-marker="content">
					</div>
				</div>

				<button class="btn btn-bulky" data-action="" >
					<div class="icon custom small" aria-label="">
						<img src="" />
					</div>
					<div>
						<span class="bulky-label"></span>
					</div>
				</button>

			</div>
		</div>
EOT;
        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }
}
