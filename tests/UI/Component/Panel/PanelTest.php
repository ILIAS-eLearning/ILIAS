<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation as I;

class ComponentDummy implements C\Component
{
    public function __construct($id = "")
    {
        $this->id = $id;
    }
    public function getCanonicalName()
    {
        return "Component Dummy";
    }
}

/**
 * Test on button implementation.
 */
class PanelTest extends ILIAS_UI_TestBase
{

    /**
     * @return \ILIAS\UI\Implementation\Component\Panel\Factory
     */
    public function getPanelFactory()
    {
        return new I\Component\Panel\Factory(
            $this->createMock(C\Panel\Listing\Factory::class)
        );
    }

    public function test_implements_factory_interface()
    {
        $f = $this->getPanelFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Panel\\Factory", $f);
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\Panel\\Standard",
            $f->standard("Title", array(new ComponentDummy()))
        );
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\Panel\\Sub",
            $f->sub("Title", array(new ComponentDummy()))
        );
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\Panel\\Report",
            $f->report("Title", $f->sub("Title", array(new ComponentDummy())))
        );
    }

    public function test_standard_get_title()
    {
        $f = $this->getPanelFactory();
        $p = $f->standard("Title", array(new ComponentDummy()));

        $this->assertEquals($p->getTitle(), "Title");
    }

    public function test_standard_get_content()
    {
        $f = $this->getPanelFactory();
        $c = new ComponentDummy();
        $p = $f->standard("Title", array($c));

        $this->assertEquals($p->getContent(), array($c));
    }

    public function test_standard_with_actions()
    {
        $fp = $this->getPanelFactory();

        $p = $fp->standard("Title", array(new ComponentDummy()));

        $actions = new I\Component\Dropdown\Standard(array(
            new I\Component\Button\Shy("ILIAS", "https://www.ilias.de"),
            new I\Component\Button\Shy("GitHub", "https://www.github.com")
        ));

        $p = $p->withActions($actions);

        $this->assertEquals($p->getActions(), $actions);
    }

    public function test_sub_with_actions()
    {
        $fp = $this->getPanelFactory();

        $p = $fp->sub("Title", array(new ComponentDummy()));

        $actions = new I\Component\Dropdown\Standard(array(
            new I\Component\Button\Shy("ILIAS", "https://www.ilias.de"),
            new I\Component\Button\Shy("GitHub", "https://www.github.com")
        ));

        $p = $p->withActions($actions);

        $this->assertEquals($p->getActions(), $actions);
    }

    public function test_sub_with_card()
    {
        $fp = $this->getPanelFactory();

        $p = $fp->sub("Title", array(new ComponentDummy()));

        $card = new I\Component\Card\Card("Card Title");

        $p = $p->withCard($card);

        $this->assertEquals($p->getCard(), $card);
    }

    public function test_report_get_title()
    {
        $f = $this->getPanelFactory();
        $sub = $f->sub("Title", array(new ComponentDummy()));
        $p = $f->report("Title", array($sub));

        $this->assertEquals($p->getTitle(), "Title");
    }

    public function test_report_get_content()
    {
        $f = $this->getPanelFactory();
        $sub = $f->sub("Title", array(new ComponentDummy()));
        $p = $f->report("Title", $sub);

        $this->assertEquals($p->getContent(), array($sub));
    }


    public function test_render_standard()
    {
        $f = $this->getPanelFactory();
        $r = $this->getDefaultRenderer();

        $actions = new I\Component\Dropdown\Standard(array(
            new I\Component\Button\Shy("ILIAS", "https://www.ilias.de"),
            new I\Component\Button\Shy("GitHub", "https://www.github.com")
        ));

        $p = $f->standard("Title", array())->withActions($actions);

        $html = $r->render($p);

        $expected_html = <<<EOT
<div class="panel panel-primary">
	<div class="panel-heading ilHeader clearfix">
		<h3 class="ilHeader">Title</h3>
		<div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"  aria-label="actions" aria-haspopup="true" aria-expanded="false"> <span class="caret"></span></button>
			<ul class="dropdown-menu">
				<li><button class="btn btn-link" data-action="https://www.ilias.de" id="id_1">ILIAS</button></li>
				<li><button class="btn btn-link" data-action="https://www.github.com" id="id_2">GitHub</button></li>
			</ul>
		</div>
	</div>
	<div class="panel-body"></div>
</div>
EOT;
        $this->assertHTMLEquals($expected_html, $html);
    }

    public function test_render_sub()
    {
        $fp = $this->getPanelFactory();
        $r = $this->getDefaultRenderer();

        $actions = new I\Component\Dropdown\Standard(array(
            new I\Component\Button\Shy("ILIAS", "https://www.ilias.de"),
            new I\Component\Button\Shy("GitHub", "https://www.github.com")
        ));

        $p = $fp->sub("Title", array())->withActions($actions);
        $card = new I\Component\Card\Card("Card Title");
        $p = $p->withCard($card);
        $html = $r->render($p);

        $expected_html = <<<EOT
<div class="panel panel-primary">
	<div class="panel-heading ilBlockHeader clearfix">
		<h4>Title</h4>
		<div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"  aria-label="actions" aria-haspopup="true" aria-expanded="false"> <span class="caret"></span></button>
			<ul class="dropdown-menu">
				<li><button class="btn btn-link" data-action="https://www.ilias.de" id="id_1">ILIAS</button></li>
				<li><button class="btn btn-link" data-action="https://www.github.com" id="id_2">GitHub</button></li>
			</ul>
		</div>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-sm-8"></div>
			<div class="col-sm-4">
				<div class="il-card thumbnail">
					<div class="card-no-highlight"></div>
					<div class="caption">
						<h5 class="card-title">Card Title</h5>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
EOT;

        $this->assertHTMLEquals($expected_html, $html);
    }
    public function test_render_report()
    {
        $fp = $this->getPanelFactory();
        $r = $this->getDefaultRenderer();
        $sub = $fp->sub("Title", array());
        $card = new I\Component\Card\Card("Card Title");
        $sub = $sub->withCard($card);
        $report = $fp->report("Title", $sub);

        $html = $r->render($report);

        $expected_html =
                "<div class=\"panel panel-primary il-panel-report\">" .
                "   <div class=\"panel-heading ilHeader\">" .
                "<h3 class=\"ilHeader\">Title</h3>" .
                "   </div>" .
                "   <div class=\"panel-body\">" .
                "
             <div class=\"panel panel-primary\">" .
                "           <div class=\"panel-heading ilBlockHeader clearfix\">" .
                "               <h4>Title</h4>" .
                "           </div>" .
                "           <div class=\"panel-body\"><div class=\"row\">" .
                "               <div class=\"col-sm-8\"></div>" .
                "               <div class=\"col-sm-4\">" .
                "                   <div class=\"il-card thumbnail\"><div class=\"card-no-highlight\"></div><div class=\"caption\"><h5 class=\"card-title\">Card Title</h5></div></div>" .
                "               </div>" .
                "           </div></div>" .
                "       </div>" .
                "   </div>" .
                "</div>";

        $this->assertHTMLEquals($expected_html, $html);
    }
}
