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

/**
 * Test items groups
 */
class ItemGroupTest extends ILIAS_UI_TestBase
{
    public function getFactory(): C\Item\Factory
    {
        return new I\Component\Item\Factory();
    }

    public function testImplementsFactoryInterface(): void
    {
        $f = $this->getFactory();

        $group = $f->group("group", array(
            $f->standard("title1"),
            $f->standard("title2")
        ));

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Item\\Group", $group);
    }

    public function testGetTitle(): void
    {
        $f = $this->getFactory();
        $c = $f->group("group", array(
            $f->standard("title1"),
            $f->standard("title2")
        ));

        $this->assertEquals("group", $c->getTitle());
    }

    public function testGetItems(): void
    {
        $f = $this->getFactory();

        $items = array(
            $f->standard("title1"),
            $f->standard("title2")
        );

        $c = $f->group("group", $items);

        $this->assertEquals($c->getItems(), $items);
    }

    public function testWithActions(): void
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

    public function testRenderBase(): void
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
  <h3>group</h3>
  <div class="dropdown">
    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" id="id_3" aria-label="actions" aria-haspopup="true" aria-expanded="false" aria-controls="id_3_menu">
     <span class="caret"></span>
    </button>
    <ul id="id_3_menu" class="dropdown-menu">
      <li>
        <button class="btn btn-link" data-action="https://www.ilias.de" id="id_1">ILIAS</button>
      </li>
      <li>
        <button class="btn btn-link" data-action="https://www.github.com" id="id_2">GitHub</button>
      </li>
    </ul>
  </div>
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
EOT;
        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }
}
