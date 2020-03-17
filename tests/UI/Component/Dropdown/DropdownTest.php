<?php

/* Copyright (c) 2017 Alexander Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation as I;

/**
 * Test on card implementation.
 */
class DropdownTest extends ILIAS_UI_TestBase
{
    protected function getFactory()
    {
        return new I\Component\Dropdown\Factory();
    }

    public function test_implements_factory_interface()
    {
        $f = $this->getFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Dropdown\\Standard", $f->standard(array()));
    }

    public function test_with_label()
    {
        $f = $this->getFactory();

        $c = $f->standard(array())->withLabel("label");

        $this->assertEquals($c->getLabel(), "label");
    }

    public function test_with_items()
    {
        $f = $this->getFactory();
        $c = $f->standard(array(
            new I\Component\Button\Shy("ILIAS", "https://www.ilias.de"),
            new I\Component\Button\Shy("GitHub", "https://www.github.com"),
            new I\Component\Divider\Horizontal(),
            new I\Component\Button\Shy("GitHub", "https://www.github.com")
        ));
        $items = $c->getItems();

        $this->assertTrue(is_array($items));
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Button\\Shy", $items[0]);
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Divider\\Horizontal", $items[2]);
    }

    public function test_render_empty()
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $c = $f->standard(array());

        $html = $r->render($c);
        $expected = "";

        $this->assertEquals($expected, $html);
    }

    public function test_render_items()
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $c = $f->standard(array(
            new I\Component\Button\Shy("ILIAS", "https://www.ilias.de"),
            new I\Component\Divider\Horizontal(),
            new I\Component\Button\Shy("GitHub", "https://www.github.com")
        ));

        $html = $r->render($c);

        $expected = <<<EOT
			<div class="dropdown">
				<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-label="actions" aria-haspopup="true" aria-expanded="false">
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu">
					<li><button class="btn btn-link" data-action="https://www.ilias.de" id="id_1">ILIAS</button></li>
					<li><hr  /></li>
					<li><button class="btn btn-link" data-action="https://www.github.com" id="id_2">GitHub</button></li>
				</ul>
			</div>
EOT;

        $this->assertHTMLEquals($expected, $html);
    }

    public function test_render_items_with_label()
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $c = $f->standard(array(
            new I\Component\Button\Shy("ILIAS", "https://www.ilias.de"),
            new I\Component\Divider\Horizontal(),
            new I\Component\Button\Shy("GitHub", "https://www.github.com")
        ))->withLabel("label");

        $html = $r->render($c);

        $expected = <<<EOT
			<div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"  aria-haspopup="true" aria-expanded="false">label <span class="caret"></span></button>
				<ul class="dropdown-menu">
					<li><button class="btn btn-link" data-action="https://www.ilias.de" id="id_1">ILIAS</button></li>
					<li><hr  /></li>
					<li><button class="btn btn-link" data-action="https://www.github.com" id="id_2">GitHub</button></li>
				</ul>
			</div>
EOT;

        $this->assertHTMLEquals($expected, $html);
    }

    public function test_render_items_with_aria_label()
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $c = $f->standard(array(
            new I\Component\Button\Shy("ILIAS", "https://www.ilias.de"),
            new I\Component\Divider\Horizontal(),
            new I\Component\Button\Shy("GitHub", "https://www.github.com")
        ))->withLabel("label")->withAriaLabel("my_aria_label");

        $html = $r->render($c);

        $expected = <<<EOT
			<div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"  aria-label="my_aria_label" aria-haspopup="true" aria-expanded="false">label <span class="caret"></span></button>
				<ul class="dropdown-menu">
					<li><button class="btn btn-link" data-action="https://www.ilias.de" id="id_1">ILIAS</button></li>
					<li><hr  /></li>
					<li><button class="btn btn-link" data-action="https://www.github.com" id="id_2">GitHub</button></li>
				</ul>
			</div>
EOT;

        $this->assertHTMLEquals($expected, $html);
    }
}
