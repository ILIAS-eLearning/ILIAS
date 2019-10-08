<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation as I;

/**
 * Test listing panels
 */
class PanelListingTest extends ILIAS_UI_TestBase
{

    /**
     * @return \ILIAS\UI\Implementation\Factory
     */
    public function getFactory()
    {
        return new I\Component\Panel\Listing\Factory();
    }

    public function test_implements_factory_interface()
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

    public function test_get_title_get_groups()
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

        $this->assertEquals($c->getTitle(), "title");
        $this->assertEquals($c->getItemGroups(), $groups);
    }

    public function test_with_actions()
    {
        $f = $this->getFactory();

        $actions = new I\Component\Dropdown\Standard(array(
            new I\Component\Button\Shy("ILIAS", "https://www.ilias.de"),
            new I\Component\Button\Shy("GitHub", "https://www.github.com")
        ));

        $groups = array();

        $c = $f->standard("title", $groups)
            ->withActions($actions);

        $this->assertEquals($c->getActions(), $actions);
    }

    public function test_render_base()
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
<div class="il-panel-listing-std-container clearfix">
	<h3>title</h3>
	<div class="il-item-group">
		<h4>Subtitle 1</h4>
		<div class="il-item-group-items">
			<div class="il-panel-listing-std-item-container"><div class="il-item il-std-item ">	
				<h5>title1</h5>
			</div></div><div class="il-panel-listing-std-item-container"><div class="il-item il-std-item ">
				<h5>title2</h5>
			</div></div>
		</div>
	</div><div class="il-item-group">
		<h4>Subtitle 2</h4>
	<div class="il-item-group-items">
	<div class="il-panel-listing-std-item-container"><div class="il-item il-std-item ">
			<h5>title3</h5>
		</div></div>
	</div>
</div>
</div>
EOT;
        $this->assertHTMLEquals($expected, $html);
    }

    public function test_render_with_actions()
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
<div class="il-panel-listing-std-container clearfix">
<h3>title</h3><div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"  aria-haspopup="true" aria-expanded="false"> <span class="caret"></span></button>
<ul class="dropdown-menu">
	<li><button class="btn btn-link" data-action="https://www.ilias.de" id="id_1">ILIAS</button></li>
	<li><button class="btn btn-link" data-action="https://www.github.com" id="id_2">GitHub</button></li>
</ul>
</div>
</div>
EOT;
        $this->assertHTMLEquals($expected, $html);
    }
}
