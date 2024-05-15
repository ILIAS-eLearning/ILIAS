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

require_once(__DIR__ . "/../../../../../../vendor/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation as I;

/**
 * Test listing panels
 */
class PanelListingTest extends ILIAS_UI_TestBase
{
    public function getUIFactory(): NoUIFactory
    {
        return new class () extends NoUIFactory {
            public function button(): I\Component\Button\Factory
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

    public function getFactory(): C\Panel\Listing\Factory
    {
        return new I\Component\Panel\Listing\Factory();
    }

    public function testImplementsFactoryInterface(): void
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

    public function testGetTitleGetGroups(): void
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

    public function testWithActions(): void
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

    public function testRenderBase(): void
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
<div class="panel panel-flex il-panel-listing-std-container clearfix">
<div class="panel-heading ilHeader">
<div class="panel-title"><h2>title</h2></div><div class="panel-controls"></div></div>
<div class="panel-listing-body">
<div class="il-item-group">
<h3>Subtitle 1</h3>
<div class="il-item-group-items">
    <ul>
          <li class="il-std-item-container">
            <div class="il-item il-std-item ">
              <h4 class="il-item-title">title1</h4>
            </div>
          </li>
          <li class="il-std-item-container">
            <div class="il-item il-std-item ">
              <h4 class="il-item-title">title2</h4>
            </div>
          </li>
  </ul>
</div>
</div>
<div class="il-item-group">
<h3>Subtitle 2</h3>
<div class="il-item-group-items">
  <ul>
        <li class="il-std-item-container">
            <div class="il-item il-std-item ">
              <h4 class="il-item-title">title3</h4>
            </div>
        </li>
  </ul>
</div>
</div>
</div>
</div>
EOT;
        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function testRenderWithActions(): void
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
<div class="panel panel-flex il-panel-listing-std-container clearfix">
<div class="panel-heading ilHeader">
<div class="panel-title"><h2>title</h2></div><div class="panel-controls"><div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" id="id_3" aria-label="actions" aria-haspopup="true" aria-expanded="false" aria-controls="id_3_menu"> <span class="caret"></span></button>
<ul id="id_3_menu" class="dropdown-menu">
	<li><button class="btn btn-link" data-action="https://www.ilias.de" id="id_1">ILIAS</button></li>
	<li><button class="btn btn-link" data-action="https://www.github.com" id="id_2">GitHub</button></li>
</ul>
</div>
</div>
</div>
<div class="panel-listing-body"></div>
</div>
EOT;
        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function testRenderWithExpanded(): void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $uri = new ILIAS\Data\URI("http://www.ilias.de");
        $c = $f->standard("title", [])
            ->withExpandable(true, $uri, $uri);

        $html = $r->render($c);

        $expected = <<<EOT
<div class="panel panel-flex panel-expandable il-panel-listing-std-container clearfix">
    <div class="panel-heading ilHeader">
        <div class="panel-opener" data-toggle="collapse" data-target="#id_1_body">
            <h2>
                <div class="panel-collapse-button">
                    <button class="btn btn-bulky" data-action="" id="id_2">
                        <span class="glyph" role="img">
                            <span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span>
                        </span>
                        <span class="bulky-label">title</span>
                    </button>
                </div>
                <div class="panel-expand-button">
                    <button class="btn btn-bulky" data-action="" id="id_3">
                        <span class="glyph" role="img">
                            <span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span>
                        </span>
                        <span class="bulky-label">title</span>
                    </button>
                </div>
            </h2>
        </div>
        <div class="panel-controls"></div>
    </div>
    <div class="panel-listing-body panel-body-expandable collapse in" id="id_1_body"></div>
</div>
EOT;
        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function testRenderWithCollapsed(): void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $uri = new ILIAS\Data\URI("http://www.ilias.de");
        $c = $f->standard("title", [])
               ->withExpandable(false, $uri, $uri);

        $html = $r->render($c);

        $expected = <<<EOT
<div class="panel panel-flex panel-expandable il-panel-listing-std-container clearfix">
    <div class="panel-heading ilHeader">
        <div class="panel-opener" data-toggle="collapse" data-target="#id_1_body">
            <h2>
                <div class="panel-collapse-button">
                    <button class="btn btn-bulky" data-action="" id="id_2">
                        <span class="glyph" role="img">
                            <span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span>
                        </span>
                        <span class="bulky-label">title</span>
                    </button>
                </div>
                <div class="panel-expand-button">
                    <button class="btn btn-bulky" data-action="" id="id_3">
                        <span class="glyph" role="img">
                            <span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span>
                        </span>
                        <span class="bulky-label">title</span>
                    </button>
                </div>
            </h2>
        </div>
        <div class="panel-controls"></div>
    </div>
    <div class="panel-listing-body panel-body-expandable collapse " id="id_1_body"></div>
</div>
EOT;
        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }
}
