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
 * Test listing panels
 */
class PanelListingTest extends ILIAS_UI_TestBase
{
    public function getFactory(): C\Panel\Listing\Factory
    {
        return new I\Component\Panel\Listing\Factory();
    }

    public function test_implements_factory_interface(): void
    {
        $f = $this->getFactory();

        $std_list = $f->standard("List Title", array(
            new I\Component\Item\Group("Subtitle 1", array(
                new I\Component\Item\Standard("title1"),
                new I\Component\Item\Standard("title2")
            )),
            new I\Component\Item\Group("Subtitle 2", array(
                new I\Component\Item\Standard("title3")
            ))
        ));

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Panel\\Listing\\Standard", $std_list);
    }

    public function test_get_title_get_groups(): void
    {
        $f = $this->getFactory();

        $groups = array(
            new I\Component\Item\Group("Subtitle 1", array(
                new I\Component\Item\Standard("title1"),
                new I\Component\Item\Standard("title2")
            )),
            new I\Component\Item\Group("Subtitle 2", array(
                new I\Component\Item\Standard("title3")
            ))
        );

        $c = $f->standard("title", $groups);

        $this->assertEquals("title", $c->getTitle());
        $this->assertEquals($groups, $c->getItemGroups());
    }

    public function test_with_actions(): void
    {
        $f = $this->getFactory();

        $actions = new I\Component\Dropdown\Standard(array(
            new I\Component\Button\Shy("ILIAS", "https://www.ilias.de"),
            new I\Component\Button\Shy("GitHub", "https://www.github.com")
        ));

        $groups = array();

        $c = $f->standard("title", $groups)
            ->withActions($actions);

        $this->assertEquals($actions, $c->getActions());
    }

    public function test_render_base(): void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $groups = array(
            new I\Component\Item\Group("Subtitle 1", array(
                new I\Component\Item\Standard("title1"),
                new I\Component\Item\Standard("title2")
            )),
            new I\Component\Item\Group("Subtitle 2", array(
                new I\Component\Item\Standard("title3")
            ))
        );

        $c = $f->standard("title", $groups);

        $html = $r->render($c);

        $expected = <<<EOT
<div class="panel il-panel-listing-std-container clearfix">
	<h2>title</h2>
	<div class="il-item-group">
		<h3>Subtitle 1</h3>
		<div class="il-item-group-items">
			<div class="il-std-item-container"><div class="il-item il-std-item ">	
                <div class="il-item-title">title1</div>
			</div></div><div class="il-std-item-container"><div class="il-item il-std-item ">
                <div class="il-item-title">title2</div>
			</div></div>
		</div>
	</div><div class="il-item-group">
		<h3>Subtitle 2</h3>
	<div class="il-item-group-items">
	<div class="il-std-item-container"><div class="il-item il-std-item ">
            <div class="il-item-title">title3</div>
		</div></div>
	</div>
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

        $groups = array();

        $actions = new I\Component\Dropdown\Standard(array(
            new I\Component\Button\Shy("ILIAS", "https://www.ilias.de"),
            new I\Component\Button\Shy("GitHub", "https://www.github.com")
        ));

        $c = $f->standard("title", $groups)
            ->withActions($actions);

        $html = $r->render($c);

        $expected = <<<EOT
<div class="panel il-panel-listing-std-container clearfix">
<h2>title</h2><div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" id="id_3" aria-label="actions" aria-haspopup="true" aria-expanded="false" aria-controls="id_3_menu"> <span class="caret"></span></button>
<ul id="id_3_menu" class="dropdown-menu">
	<li><button class="btn btn-link" data-action="https://www.ilias.de" id="id_1">ILIAS</button></li>
	<li><button class="btn btn-link" data-action="https://www.github.com" id="id_2">GitHub</button></li>
</ul>
</div>
</div>
EOT;
        $this->assertHTMLEquals($expected, $html);
    }
}
