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
 * Test items groups
 */
class ItemGroupTest extends ILIAS_UI_TestBase
{
    public function getFactory(): C\Item\Factory
    {
        return new I\Component\Item\Factory();
    }

    public function test_implements_factory_interface(): void
    {
        $f = $this->getFactory();

        $group = $f->group("group", array(
            $f->standard("title1"),
            $f->standard("title2")
        ));

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Item\\Group", $group);
    }

    public function test_get_title(): void
    {
        $f = $this->getFactory();
        $c = $f->group("group", array(
            $f->standard("title1"),
            $f->standard("title2")
        ));

        $this->assertEquals("group", $c->getTitle());
    }

    public function test_get_items(): void
    {
        $f = $this->getFactory();

        $items = array(
            $f->standard("title1"),
            $f->standard("title2")
        );

        $c = $f->group("group", $items);

        $this->assertEquals($c->getItems(), $items);
    }

    public function test_with_actions(): void
    {
        $f = $this->getFactory();

        $actions = new I\Component\Dropdown\Standard(array(
            new I\Component\Button\Shy("ILIAS", "https://www.ilias.de"),
            new I\Component\Button\Shy("GitHub", "https://www.github.com")
        ));
        $items = array(
            $f->standard("title1"),
            $f->standard("title2")
        );

        $c = $f->group("group", $items)->withActions($actions);

        $this->assertEquals($c->getActions(), $actions);
    }

    public function test_render_base(): void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $items = array(
            $f->standard("title1"),
            $f->standard("title2")
        );

        $c = $f->group("group", $items);

        $html = $r->render($c);

        $expected = <<<EOT
<div class="il-item-group">
	<h3>group</h3>
		<div class="il-item-group-items">
		<div class="il-std-item-container"><div class="il-item il-std-item ">
            <div class="il-item-title">title1</div>
		</div></div><div class="il-std-item-container"><div class="il-item il-std-item ">
            <div class="il-item-title">title2</div>
		</div></div>
	</div>
</div>
EOT;
        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function test_render_with_actions(): void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $actions = new I\Component\Dropdown\Standard(array(
            new I\Component\Button\Shy("ILIAS", "https://www.ilias.de"),
            new I\Component\Button\Shy("GitHub", "https://www.github.com")
        ));
        $items = array(
            $f->standard("title1"),
            $f->standard("title2")
        );

        $c = $f->group("group", $items)->withActions($actions);

        $html = $r->render($c);

        $expected = <<<EOT
<div class="il-item-group">
<h3>group</h3><div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"  aria-label="actions" aria-haspopup="true" aria-expanded="false"> <span class="caret"></span></button>
		<ul class="dropdown-menu">
			<li><button class="btn btn-link" data-action="https://www.ilias.de" id="id_1">ILIAS</button></li>
			<li><button class="btn btn-link" data-action="https://www.github.com" id="id_2">GitHub</button></li>
		</ul>
	</div>
	<div class="il-item-group-items">
		<div class="il-std-item-container"><div class="il-item il-std-item ">
            <div class="il-item-title">title1</div>
	</div></div><div class="il-std-item-container"><div class="il-item il-std-item ">
            <div class="il-item-title">title2</div>
	</div></div>
	</div>
</div>
EOT;
        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }
}
