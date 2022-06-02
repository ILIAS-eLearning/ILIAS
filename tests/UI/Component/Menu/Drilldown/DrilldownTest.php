<?php declare(strict_types=1);

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
 
require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Implementation\Component\Menu;
use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Component as C;

/**
 * Tests for the Drilldown.
 */
class DrilldownTest extends ILIAS_UI_TestBase
{
    protected C\Symbol\Icon\Standard $icon;
    protected C\Symbol\Glyph\Glyph $glyph;
    protected C\Button\Standard $button;
    protected C\Divider\Horizontal $divider;
    protected C\Legacy\Legacy $legacy;

    public function getUIFactory() : NoUIFactory
    {
        return new class extends NoUIFactory {
            public function menu() : C\Menu\Factory
            {
                return new Menu\Factory(
                    new I\SignalGenerator()
                );
            }
            public function button() : C\Button\Factory
            {
                return new I\Button\Factory();
            }
            public function legacy(string $content) : C\Legacy\Legacy
            {
                return new I\Legacy\Legacy(
                    $content,
                    new I\SignalGenerator()
                );
            }
            public function symbol() : \ILIAS\UI\Component\Symbol\Factory
            {
                return new I\Symbol\Factory(
                    new I\Symbol\Icon\Factory(),
                    new I\Symbol\Glyph\Factory(),
                    new I\Symbol\Avatar\Factory()
                );
            }
        };
    }

    public function setUp() : void
    {
        $icon_factory = new I\Symbol\Icon\Factory();
        $glyph_factory = new I\Symbol\Glyph\Factory();
        $button_factory = new I\Button\Factory();
        $divider_factory = new I\Divider\Factory();
        $this->icon = $icon_factory->standard('', '');
        $this->glyph = $glyph_factory->user('');
        $this->button = $button_factory->standard('', '');
        $this->divider = $divider_factory->horizontal();
        $this->legacy = $this->getUIFactory()->legacy('');
    }

    public function testConstruction() : C\Menu\Drilldown
    {
        $f = $this->getUIFactory();
        $menu = $f->menu()->drilldown('root', []);
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\Menu\\Menu",
            $menu
        );
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\Menu\\Drilldown",
            $menu
        );

        $menu = $f->menu()->drilldown('root', []);

        return $menu;
    }

    /**
     * @depends testConstruction
     */
    public function testGetLabel(C\Menu\Drilldown $menu) : void
    {
        $this->assertEquals(
            'root',
            $menu->getLabel()
        );
    }

    /**
     * @depends testConstruction
     */
    public function testGetItems($menu) : void
    {
        $this->assertEquals(
            [],
            $menu->getItems()
        );
    }

    public function testWithEntries() : C\Menu\Drilldown
    {
        $f = $this->getUIFactory();
        $items = array(
            $f->menu()->sub('sub', [
                $this->button,
                $this->glyph
            ]),
            $this->divider,
            $this->button
        );
        $menu = $f->menu()->drilldown('root', $items);
        $this->assertEquals(
            $items,
            $menu->getItems()
        );
        return $menu;
    }

    public function testWithWrongEntry() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $f = $this->getUIFactory();
        $f->menu()->drilldown('label', [$this->legacy]);
    }

    /**
     * @depends testWithEntries
     */
    public function testRendering() : void
    {
        $r = $this->getDefaultRenderer();
        $f = $this->getUIFactory();

        $items = [
            $f->menu()->sub('1', [
                $f->menu()->sub('1.1', []),
                $f->menu()->sub('1.2', []),
            ]),
            $f->menu()->sub('2', [])
        ];
        $menu = $f->menu()->drilldown('root', $items);

        $html = $r->render($menu);
        $expected = file_get_contents(__DIR__ . "/drilldown_test.html");

        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    /**
     * @depends testConstruction
     */
    public function testWithPersistenceId($menu) : void
    {
        $this->assertNull($menu->getPersistenceId()) ;

        $id = "some_id";
        $this->assertEquals(
            $id,
            $menu->withPersistenceId($id)->getPersistenceId()
        );
    }
}
