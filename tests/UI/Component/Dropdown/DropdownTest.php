<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation as I;

/**
 * Test on card implementation.
 */
class DropdownTest extends ILIAS_UI_TestBase
{
    protected function getFactory(): C\Dropdown\Factory
    {
        return new I\Component\Dropdown\Factory();
    }

    public function test_implements_factory_interface(): void
    {
        $f = $this->getFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Dropdown\\Standard", $f->standard(array()));
    }

    public function test_with_label(): void
    {
        $f = $this->getFactory();

        $c = $f->standard(array())->withLabel("label");

        $this->assertEquals("label", $c->getLabel());
    }

    public function test_with_items(): void
    {
        $f = $this->getFactory();
        $link = new I\Component\Link\Standard("Link to Github", "http://www.github.com");
        $c = $f->standard(array(
            new I\Component\Button\Shy("ILIAS", "https://www.ilias.de"),
            new I\Component\Button\Shy("GitHub", "https://www.github.com"),
            new I\Component\Divider\Horizontal(),
            $link->withOpenInNewViewport(true)
        ));
        $items = $c->getItems();

        $this->assertTrue(is_array($items));
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Button\\Shy", $items[0]);
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Divider\\Horizontal", $items[2]);
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Link\\Standard", $items[3]);
    }

    public function test_render_empty(): void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $c = $f->standard(array());

        $html = $r->render($c);
        $expected = "";

        $this->assertEquals($expected, $html);
    }

    public function test_render_items(): void
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
				<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" id="id_3" aria-label="actions" aria-haspopup="true" aria-expanded="false" aria-controls="id_3_menu">
					<span class="caret"></span>
				</button>
				<ul id="id_3_menu" class="dropdown-menu">
					<li><button class="btn btn-link" data-action="https://www.ilias.de" id="id_1">ILIAS</button></li>
					<li><hr  /></li>
					<li><button class="btn btn-link" data-action="https://www.github.com" id="id_2">GitHub</button></li>
				</ul>
			</div>
EOT;

        $this->assertHTMLEquals($expected, $html);
    }

    public function test_render_items_with_label(): void
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
			<div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" id="id_3" aria-haspopup="true" aria-expanded="false" aria-controls="id_3_menu">label <span class="caret"></span></button>
				<ul id="id_3_menu" class="dropdown-menu">
					<li><button class="btn btn-link" data-action="https://www.ilias.de" id="id_1">ILIAS</button></li>
					<li><hr  /></li>
					<li><button class="btn btn-link" data-action="https://www.github.com" id="id_2">GitHub</button></li>
				</ul>
			</div>
EOT;

        $this->assertHTMLEquals($expected, $html);
    }

    public function test_render_with_link_new_viewport(): void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $link = new I\Component\Link\Standard("Link to ILIAS", "http://www.ilias.de");

        $c = $f->standard(array(
            $link->withOpenInNewViewport(true)
        ));

        $html = $r->render($c);

        $expected = <<<EOT
			<div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" id="id_1" aria-label="actions" aria-haspopup="true" aria-expanded="false" aria-controls="id_1_menu"><span class="caret"></span></button>
				<ul id="id_1_menu" class="dropdown-menu">
					<li><a href="http://www.ilias.de" target="_blank" rel="noopener">Link to ILIAS</a></li>
				</ul>
			</div>
EOT;

        $this->assertHTMLEquals($expected, $html);
    }

    public function test_render_items_with_aria_label(): void
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
			<div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" id="id_3" aria-label="my_aria_label" aria-haspopup="true" aria-expanded="false" aria-controls="id_3_menu">label <span class="caret"></span></button>
				<ul id="id_3_menu" class="dropdown-menu">
					<li><button class="btn btn-link" data-action="https://www.ilias.de" id="id_1">ILIAS</button></li>
					<li><hr  /></li>
					<li><button class="btn btn-link" data-action="https://www.github.com" id="id_2">GitHub</button></li>
				</ul>
			</div>
EOT;

        $this->assertHTMLEquals($expected, $html);
    }
}
