<?php

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

declare(strict_types=1);

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation as I;
use ILIAS\UI\Implementation\Component\SignalGenerator;

/**
 * Test secondary listing panels
 */
class PanelSecondaryListingTest extends ILIAS_UI_TestBase
{
    public function getUIFactory(): NoUIFactory
    {
        return new class () extends NoUIFactory {
            public function panelSecondary(): I\Component\Panel\Secondary\Factory
            {
                return new I\Component\Panel\Secondary\Factory();
            }

            public function dropdown(): C\Dropdown\Factory
            {
                return new I\Component\Dropdown\Factory();
            }

            public function viewControl(): C\ViewControl\Factory
            {
                return new I\Component\ViewControl\Factory(new SignalGenerator());
            }

            public function button(): C\Button\Factory
            {
                return new I\Component\Button\Factory();
            }

            public function symbol(): C\Symbol\Factory
            {
                return new I\Component\Symbol\Factory(
                    new I\Component\Symbol\Icon\Factory(),
                    new I\Component\Symbol\Glyph\Factory(),
                    new I\Component\Symbol\Avatar\Factory()
                );
            }
        };
    }

    protected function cleanHTML(string $html): string
    {
        $html = str_replace(["\n", "\t"], "", $html);

        return trim($html);
    }

    public function testImplementsFactoryInterface(): void
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

    public function testGetTitle(): void
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

        $this->assertEquals("title", $c->getTitle());
    }

    public function testGetItemGroups(): void
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

    public function testWithActions(): void
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

    public function testRenderWithActions(): void
    {
        $actions = $this->getUIFactory()->dropdown()->standard(array(
            $this->getUIFactory()->button()->shy("ILIAS", "https://www.ilias.de"),
            $this->getUIFactory()->button()->shy("Github", "https://www.github.com")
        ));

        $sec = $this->getUIFactory()->panelSecondary()->listing("Title", array())->withActions($actions);

        $html = $this->getDefaultRenderer()->render($sec);

        $expected_html = <<<EOT
<div class="panel panel-secondary panel-flex">
    <div class="panel-heading ilHeader">
        <div class="panel-title"><h2>Title</h2></div>
        <div class="panel-controls">
            <div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" id="id_3" aria-label="actions" aria-haspopup="true" aria-expanded="false" aria-controls="id_3_menu"><span class="caret"></span></button>
                <ul id="id_3_menu" class="dropdown-menu">
                    <li><button class="btn btn-link" data-action="https://www.ilias.de" id="id_1">ILIAS</button></li>
                    <li><button class="btn btn-link" data-action="https://www.github.com" id="id_2">Github</button></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="panel-body">
    </div>
</div>
EOT;
        $this->assertEquals(
            $this->brutallyTrimHTML($expected_html),
            $this->brutallyTrimHTML($html)
        );
    }

    public function testRenderWithSortation(): void
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
    <div class="panel-heading ilHeader">
        <div class="panel-title"><h2>Title</h2></div>
        <div class="panel-controls"></div>
    </div>
    <div class="panel-viewcontrols l-bar__container">
        <div class="il-viewcontrol-sortation l-bar__element" id="id_1">
            <div class="dropdown">
                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" id="id_4" aria-label="actions" aria-haspopup="true" aria-expanded="false" aria-controls="id_4_menu">
                    <span class="caret"></span>
                </button>
                <ul id="id_4_menu" class="dropdown-menu">
                    <li><button class="btn btn-link" data-action="?sortation=a" id="id_2">A</button></li>
                    <li><button class="btn btn-link" data-action="?sortation=b" id="id_3">B</button></li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="panel-body">
    </div>
</div>
EOT;
        $this->assertEquals(
            $this->brutallyTrimHTML($expected_html),
            $this->brutallyTrimHTML($html)
        );
    }

    public function testRenderWithPagination(): void
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
    <div class="panel-heading ilHeader">
        <div class="panel-title"><h2>Title</h2></div>
        <div class="panel-controls"></div>
    </div>
    <div class="panel-viewcontrols l-bar__container">
        <div class="il-viewcontrol-pagination l-bar__element">
            <span class="btn btn-ctrl browse previous">
                <a tabindex="0" class="glyph" href="http://ilias.de?page=0" aria-label="back">
                    <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
                </a>
            </span>
            <button class="btn btn-link" data-action="http://ilias.de?page=0" id="id_1">1</button>
            <button class="btn btn-link engaged" aria-pressed="true" data-action="http://ilias.de?page=1" id="id_2">2</button>
            <button class="btn btn-link" data-action="http://ilias.de?page=2" id="id_3">3</button>
            <button class="btn btn-link" data-action="http://ilias.de?page=3" id="id_4">4</button>
            <button class="btn btn-link" data-action="http://ilias.de?page=4" id="id_5">5</button>
            <span class="btn btn-ctrl browse next">
                <a tabindex="0" class="glyph" href="http://ilias.de?page=2" aria-label="next">
                    <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                </a>
            </span>
        </div>
    </div>
    <div class="panel-body">
    </div>
</div>
EOT;
        $this->assertEquals(
            $this->brutallyTrimHTML($expected_html),
            $this->brutallyTrimHTML($html)
        );
    }

    public function testRenderWithSection(): void
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
    <div class="panel-heading ilHeader">
        <div class="panel-title"><h2>Title</h2></div>
        <div class="panel-controls"></div>
    </div>
    <div class="panel-viewcontrols l-bar__container">
        <div class="il-viewcontrol-section l-bar__element">
            <a class="btn btn-ctrl browse previous" href="http://www.ilias.de" aria-label="previous" data-action="http://www.ilias.de" id="id_1">
                <span class="glyphicon glyphicon-chevron-left"></span>
            </a>
            <button class="btn btn-default" data-action="">current</button>
            <a class="btn btn-ctrl browse next" href="http://www.github.com" aria-label="next" data-action="http://www.github.com" id="id_2">
                <span class="glyphicon glyphicon-chevron-right"></span>
            </a>
        </div>
    </div>
    <div class="panel-body">
    </div>
</div>
EOT;
        $this->assertEquals(
            $this->brutallyTrimHTML($expected_html),
            $this->brutallyTrimHTML($html)
        );
    }
    public function testRenderWithFooter(): void
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

    public function testRenderWithNoHeaderButContent(): void
    {
        $group = new I\Component\Item\Group(
            "Subtitle 1",
            array(
                new I\Component\Item\Standard("title1"),
                new I\Component\Item\Standard("title2"))
        );

        $secondary_panel = $this->getUIFactory()->panelSecondary()->listing("", array($group));

        $html = $this->getDefaultRenderer()->render($secondary_panel);

        $expected_html = <<<EOT
<div class="panel panel-secondary panel-flex">
  <div class="panel-body">
    <div class="il-item-group">
      <h3>Subtitle 1</h3>
      <div class="il-item-group-items">
        <ul>
            <li class="il-std-item-container">
              <div class="il-item il-std-item ">
                <div class="il-item-title">title1</div>
              </div>
            </li>
            <li class="il-std-item-container">
              <div class="il-item il-std-item ">
                <div class="il-item-title">title2</div>
              </div>
            </li>
        </ul>
      </div>
    </div>
  </div>
</div>
EOT;
        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected_html),
            $this->brutallyTrimHTML($html)
        );
    }

    public function testRenderWithNoHeaderNoContentNoFooter(): void
    {
        $secondary_panel = $this->getUIFactory()->panelSecondary()->listing("", array());

        $html = $this->getDefaultRenderer()->render($secondary_panel);

        $this->assertEquals("", $html);
    }
}
