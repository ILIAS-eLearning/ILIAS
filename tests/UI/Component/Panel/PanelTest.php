<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation as I;
use \ILIAS\UI\Implementation\Component\SignalGenerator;

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
<div class="panel panel-primary panel-flex">
	<div class="panel-heading ilHeader">
		<h2>Title</h2>
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
        $html = $this->brutallyTrimHTML($r->render($p));

        $expected_html = <<<EOT
<div class="panel panel-sub panel-flex">
	<div class="panel-heading ilBlockHeader">
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
                    <div class="caption card-title">Card Title</div>
                </div>
			</div>
		</div>
	</div>
</div>
EOT;

        $this->assertHTMLEquals($this->brutallyTrimHTML($expected_html), $html);
    }

    public function test_render_report()
    {
        $fp = $this->getPanelFactory();
        $r = $this->getDefaultRenderer();
        $sub = $fp->sub("Title", array());
        $card = new I\Component\Card\Card("Card Title");
        $sub = $sub->withCard($card);
        $report = $fp->report("Title", $sub);

        $html = $this->brutallyTrimHTML($r->render($report));

        $expected_html = <<<EOT
<div class="panel panel-primary il-panel-report panel-flex">
    <div class="panel-heading ilHeader">
        <h3>Title</h3>
    </div>
    <div class="panel-body">
        <div class="panel panel-sub panel-flex">
            <div class="panel-heading ilBlockHeader">
                <h4>Title</h4>
            </div>
            <div class="panel-body"><div class="row">
                <div class="col-sm-8"></div>
                    <div class="col-sm-4">
                        <div class="il-card thumbnail">
                            <div class="card-no-highlight"></div>
                            <div class="caption card-title">Card Title</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
EOT;

        $this->assertHTMLEquals($this->brutallyTrimHTML($expected_html), $html);
    }

    public function test_with_view_controls()
    {
        $sort_options = [
            'a' => 'A',
            'b' => 'B'
        ];
        $sortation = $this->getUIFactory()->viewControl()->sortation($sort_options);
        $f = $this->getPanelFactory();
        $p = $f->standard("Title", [])
            ->withViewControls([$sortation]);
        ;

        $this->assertEquals($p->getViewControls(), [$sortation]);
    }

    public function test_render_with_sortation()
    {
        $sort_options = [
            'a' => 'A',
            'b' => 'B'
        ];
        $sortation = $this->getUIFactory()->viewControl()->sortation($sort_options);

        $f = $this->getPanelFactory();
        $r = $this->getDefaultRenderer();


        $p = $f->standard("Title", [])
            ->withViewControls([$sortation]);
        ;

        $html = $r->render($p);

        $expected_html = <<<EOT
<div class="panel panel-primary panel-flex">
	<div class="panel-heading ilHeader">
		<h2>Title</h2> 
		<div class="il-viewcontrol-sortation" id="id_1">
<div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"  aria-label="actions" aria-haspopup="true" aria-expanded="false" > <span class="caret"></span></button>
<ul class="dropdown-menu">
	<li><button class="btn btn-link" data-action="?sortation=a" id="id_2">A</button>
</li>
	<li><button class="btn btn-link" data-action="?sortation=b" id="id_3">B</button>
</li>
</ul>
</div>
</div>
	</div>
	<div class="panel-body"></div>
</div>
EOT;
        $this->assertHTMLEquals($expected_html, $html);
    }

    public function test_render_with_pagination()
    {
        $pagination = $this->getUIFactory()->viewControl()->pagination()
            ->withTargetURL('http://ilias.de', 'page')
            ->withTotalEntries(10)
            ->withPageSize(2)
            ->withCurrentPage(1);

        $f = $this->getPanelFactory();
        $r = $this->getDefaultRenderer();


        $p = $f->standard("Title", [])
            ->withViewControls([$pagination]);

        $html = $r->render($p);

        $expected_html = <<<EOT
<div class="panel panel-primary panel-flex">
	<div class="panel-heading ilHeader">
		<h2>Title</h2> 
		<div class="il-viewcontrol-pagination">
<span class="browse previous"><a class="glyph" href="http://ilias.de?page=0" aria-label="back">
<span class="glyphicon
 glyphicon-chevron-left
" aria-hidden="true"></span>
</a>
</span>
 <button class="btn btn-link" data-action="http://ilias.de?page=0" id="id_1">1</button>
  <button class="btn btn-link engaged" aria-pressed="true" data-action="http://ilias.de?page=1" id="id_2">2</button>
  <button class="btn btn-link" data-action="http://ilias.de?page=2" id="id_3">3</button>
  <button class="btn btn-link" data-action="http://ilias.de?page=3" id="id_4">4</button>
  <button class="btn btn-link" data-action="http://ilias.de?page=4" id="id_5">5</button>
<span class="browse next"><a class="glyph" href="http://ilias.de?page=2" aria-label="next">
<span class="glyphicon
 glyphicon-chevron-right
" aria-hidden="true"></span>
</a>
</span>
</div>
		
	</div>
	<div class="panel-body"></div>
</div>
EOT;
        $this->assertHTMLEquals($expected_html, $html);
    }
}
