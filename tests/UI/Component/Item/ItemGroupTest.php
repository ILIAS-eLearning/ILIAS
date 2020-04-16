<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation as I;

/**
 * Test items groups
 */
class ItemGroupTest extends ILIAS_UI_TestBase
{

    /**
     * @return \ILIAS\UI\Implementation\Factory
     */
    public function getFactory()
    {
        return new I\Component\Item\Factory;
    }

    public function test_implements_factory_interface()
    {
        $f = $this->getFactory();

        $group = $f->group("group", array(
            $f->standard("title1"),
            $f->standard("title2")
        ));

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Item\\Group", $group);
    }

    public function test_get_title()
    {
        $f = $this->getFactory();
        $c = $f->group("group", array(
            $f->standard("title1"),
            $f->standard("title2")
        ));

        $this->assertEquals($c->getTitle(), "group");
    }

    public function test_get_items()
    {
        $f = $this->getFactory();

        $items = array(
            $f->standard("title1"),
            $f->standard("title2")
        );

        $c = $f->group("group", $items);

        $this->assertEquals($c->getItems(), $items);
    }

    public function test_with_actions()
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

    public function test_render_base()
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
	<h4>group</h4>
	<div class="il-item-group-items">
		<div class="il-panel-listing-std-item-container"><div class="il-item il-std-item ">
			<h5>title1</h5>
		</div></div><div class="il-panel-listing-std-item-container"><div class="il-item il-std-item ">
			<h5>title2</h5>
		</div></div>
	</div>
</div>
EOT;
        $this->assertHTMLEquals($expected, $html);
    }

    public function test_render_with_actions()
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
<h4>group</h4><div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"  aria-label="actions" aria-haspopup="true" aria-expanded="false"> <span class="caret"></span></button>
		<ul class="dropdown-menu">
			<li><button class="btn btn-link" data-action="https://www.ilias.de" id="id_1">ILIAS</button></li>
			<li><button class="btn btn-link" data-action="https://www.github.com" id="id_2">GitHub</button></li>
		</ul>
	</div>
	<div class="il-item-group-items">
		<div class="il-panel-listing-std-item-container"><div class="il-item il-std-item ">
			<h5>title1</h5>
	</div></div><div class="il-panel-listing-std-item-container"><div class="il-item il-std-item ">
			<h5>title2</h5>
	</div></div>
	</div>
</div>
EOT;
        $this->assertHTMLEquals($expected, $html);
    }
}
