<?php

/* Copyright (c) 2019 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation as I;
use \ILIAS\UI\Implementation\Component\SignalGenerator;

/**
 * Test secondary listing panels
 */
class PanelSecondaryListingTest extends ILIAS_UI_TestBase
{
    public function getUIFactory()
    {
        $factory = new class extends NoUIFactory {
            public function panelSecondary()
            {
                return new I\Component\Panel\Secondary\Factory();
            }
            public function dropdown()
            {
                return new I\Component\Dropdown\Factory();
            }
            public function viewControl()
            {
                return new I\Component\ViewControl\Factory(new SignalGenerator());
            }
            public function button()
            {
                return new I\Component\Button\Factory();
            }
            public function symbol() : C\Symbol\Factory
            {
                return new I\Component\Symbol\Factory(
                    new I\Component\Symbol\Icon\Factory(),
                    new I\Component\Symbol\Glyph\Factory(),
                    new I\Component\Symbol\Avatar\Factory()
                );
            }
        };
        return $factory;
    }

    protected function cleanHTML($html)
    {
        $html = str_replace(["\n", "\t"], "", $html);

        return trim($html);
    }

    public function test_implements_factory_interface()
    {
        $secondary_panel = $this->getUIFactory()->panelSecondary()->listing("List Title", array(

            new I\Component\Item\Group("Subtitle 1", array(
                new I\Component\Item\Standard("title1"),
                new I\Component\Item\Standard("title2")
            )),
            new I\Component\Item\Group("Subtitle 2", array(
                new I\Component\Item\Standard("title3")
            ))
        ));

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Panel\\Secondary\\Listing", $secondary_panel);
    }

    public function test_get_title()
    {
        $groups = array(
            new I\Component\Item\Group("Subtitle 1", array(
                new I\Component\Item\Standard("title1"),
                new I\Component\Item\Standard("title2")
            )),
            new I\Component\Item\Group("Subtitle 2", array(
                new I\Component\Item\Standard("title3")
            ))
        );

        $c = $this->getUIFactory()->panelSecondary()->listing("title", $groups);

        $this->assertEquals($c->getTitle(), "title");
    }

    public function test_get_item_groups()
    {
        $groups = array(
            new I\Component\Item\Group("Subtitle 1", array(
                new I\Component\Item\Standard("title1"),
                new I\Component\Item\Standard("title2")
            )),
            new I\Component\Item\Group("Subtitle 2", array(
                new I\Component\Item\Standard("title3")
            ))
        );

        $c = $this->getUIFactory()->panelSecondary()->listing("title", $groups);

        $this->assertEquals($c->getItemGroups(), $groups);
    }

    public function test_with_actions()
    {
        $actions = new I\Component\Dropdown\Standard(array(
            new I\Component\Button\Shy("ILIAS", "https://www.ilias.de"),
            new I\Component\Button\Shy("GitHub", "https://www.github.com")
        ));

        $groups = array();

        $c = $this->getUIFactory()->panelSecondary()->listing("title", $groups)
            ->withActions($actions);

        $this->assertEquals($c->getActions(), $actions);
    }

    //RENDER

    public function test_render_with_actions()
    {
        $actions = $this->getUIFactory()->dropdown()->standard(array(
            $this->getUIFactory()->button()->shy("ILIAS", "https://www.ilias.de"),
            $this->getUIFactory()->button()->shy("Github", "https://www.github.com")
        ));

        $sec = $this->getUIFactory()->panelSecondary()->listing("Title", array())->withActions($actions);

        $html = $this->getDefaultRenderer()->render($sec);

        $expected_html = <<<EOT
<div class="panel panel-secondary panel-flex">
	<div class="panel-heading ilHeader clearfix">
		<h4 class="ilHeader">Title</h4>
		<div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"  aria-label="actions" aria-haspopup="true" aria-expanded="false"> <span class="caret"></span></button>
			<ul class="dropdown-menu">
				<li><button class="btn btn-link" data-action="https://www.ilias.de" id="id_1">ILIAS</button></li>
				<li><button class="btn btn-link" data-action="https://www.github.com" id="id_2">Github</button></li>
			</ul>
		</div>
	</div>
	<div class="panel-body">
	</div>
</div>
EOT;
        $this->assertHTMLEquals(
            $this->cleanHTML($expected_html),
            $this->cleanHTML($html)
        );
    }

    public function test_render_with_sortation()
    {
        $sort_options = array(
            'a' => 'A',
            'b' => 'B'
        );
        $sortation = $this->getUIFactory()->viewControl()->sortation($sort_options);
        $sec = $this->getUIFactory()->panelSecondary()->listing("Title", array())
            ->withViewControls([$sortation]);

        $html = $this->getDefaultRenderer()->render($sec);

        $expected_html = <<<EOT
<div class="panel panel-secondary panel-flex">
	<div class="panel-heading ilHeader clearfix">
		<h4 class="ilHeader">Title</h4>
		<div class="il-viewcontrol-sortation" id="">
			<div class="dropdown">
				<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-label="actions" aria-haspopup="true" aria-expanded="false">
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu">
					<li><button class="btn btn-link" data-action="?sortation=a" id="id_1">A</button></li>
					<li><button class="btn btn-link" data-action="?sortation=b" id="id_2">B</button></li>
				</ul>
			</div>
		</div>	
	</div>
	<div class="panel-body">
	</div>
</div>
EOT;
        $this->assertHTMLEquals(
            $this->cleanHTML($expected_html),
            $this->cleanHTML($html)
        );
    }

    public function test_render_with_pagination()
    {
        $pagination = $this->getUIFactory()->viewControl()->pagination()
            ->withTargetURL('http://ilias.de', 'page')
            ->withTotalEntries(10)
            ->withPageSize(2)
            ->withCurrentPage(1);

        $sec = $this->getUIFactory()->panelSecondary()->listing("Title", array())
            ->withViewControls([$pagination]);

        $html = $this->getDefaultRenderer()->render($sec);

        $expected_html = <<<EOT
<div class="panel panel-secondary panel-flex">
	<div class="panel-heading ilHeader clearfix">
		<h4 class="ilHeader">Title</h4>
		<div class="il-viewcontrol-pagination">
			<span class="browse previous">
				<a class="glyph" href="http://ilias.de?page=0" aria-label="back">
					<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
				</a>
			</span>
			<button class="btn btn-link" data-action="http://ilias.de?page=0" id="id_1">1</button>
			<button class="btn btn-link" data-action="http://ilias.de?page=1" disabled="true">2</button>
			<button class="btn btn-link" data-action="http://ilias.de?page=2" id="id_2">3</button>
			<button class="btn btn-link" data-action="http://ilias.de?page=3" id="id_3">4</button>
			<button class="btn btn-link" data-action="http://ilias.de?page=4" id="id_4">5</button>
			<span class="browse next">
				<a class="glyph" href="http://ilias.de?page=2" aria-label="next">
					<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
				</a>
			</span>
		</div>
	</div>
	<div class="panel-body">
	</div>
</div>
EOT;
        $this->assertHTMLEquals(
            $this->cleanHTML($expected_html),
            $this->cleanHTML($html)
        );
    }

    public function test_render_with_section()
    {
        $back = $this->getUIFactory()->button()->standard("previous", "http://www.ilias.de");
        $next = $this->getUIFactory()->button()->standard("next", "http://www.github.com");
        $current = $this->getUIFactory()->button()->standard("current", "");
        $section = $this->getUIFactory()->viewControl()->section($back, $current, $next);

        $secondary_panel = $this->getUIFactory()->panelSecondary()->listing("Title", array())
            ->withViewControls([$section]);

        $html = $this->getDefaultRenderer()->render($secondary_panel);

        $expected_html = <<<EOT
<div class="panel panel-secondary panel-flex">
	<div class="panel-heading ilHeader clearfix">
		<h4 class="ilHeader">Title</h4>
		<div class="il-viewcontrol-section">
			<a class="btn btn-default " href="http://www.ilias.de" aria-label="previous" data-action="http://www.ilias.de">
				<span class="glyphicon glyphicon-chevron-left"></span>
			</a>
			<button class="btn btn-default" data-action="">
				current
			</button>
			<a class="btn btn-default " href="http://www.github.com" aria-label="next" data-action="http://www.github.com">
				<span class="glyphicon glyphicon-chevron-right"></span>
			</a>
		</div>
	</div>
	<div class="panel-body">
	</div>
</div>
EOT;
        $this->assertHTMLEquals(
            $this->cleanHTML($expected_html),
            $this->cleanHTML($html)
        );
    }
    public function test_render_with_footer()
    {
        $footer_shy_button = $this->getUIFactory()->button()->shy("Action", "");
        $secondary_panel = $this->getUIFactory()->panelSecondary()->listing("", array())->withFooter($footer_shy_button);

        $html = $this->getDefaultRenderer()->render($secondary_panel);

        $expected_html = <<<EOT
<div class="panel panel-secondary panel-flex">\n
<div class="panel-body"></div>\n
<div class="panel-footer ilBlockInfo"><button class="btn btn-link" data-action="">Action</button></div>\n
</div>\n

EOT;
        $this->assertHTMLEquals(
            $this->cleanHTML($expected_html),
            $this->cleanHTML($html)
        );
    }

    public function test_render_with_no_header_but_content()
    {

        $group = new I\Component\Item\Group("Subtitle 1", array(
                new I\Component\Item\Standard("title1"),
                new I\Component\Item\Standard("title2"))
        );

        $secondary_panel = $this->getUIFactory()->panelSecondary()->listing("", array($group));

        $html = $this->getDefaultRenderer()->render($secondary_panel);

        $expected_html = <<<EOT
<div class="panel panel-secondary panel-flex">
<div class="panel-body">
<div class="il-item-group">\n
    <h3>Subtitle 1</h3>\n
<div class="il-item-group-items">\n
<div class="il-std-item-container">
<div class="il-item il-std-item ">
<div class="il-item-title">title1</div>
</div></div>\n
<div class="il-std-item-container">
<div class="il-item il-std-item ">
<div class="il-item-title">title2</div>
</div>
</div>\n
</div>\n
</div>
</div>
</div>\n
EOT;
        $this->assertHTMLEquals(
            $this->cleanHTML($expected_html),
            $this->cleanHTML($html)
        );
    }

    public function test_render_with_no_header_no_content_no_footer()
    {

        $secondary_panel = $this->getUIFactory()->panelSecondary()->listing("", array());

        $html = $this->getDefaultRenderer()->render($secondary_panel);

        $this->assertEquals("",$html);
    }
}
