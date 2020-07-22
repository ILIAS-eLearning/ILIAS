<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation as I;

/**
 * Tests for the Breadcrumbs-component
 */
class BreadcrumbsTest extends ILIAS_UI_TestBase
{
    public function getFactory()
    {
        return new \ILIAS\UI\Implementation\Factory(
            $this->createMock(C\Counter\Factory::class),
            $this->createMock(C\Glyph\Factory::class),
            $this->createMock(C\Button\Factory::class),
            $this->createMock(C\Listing\Factory::class),
            $this->createMock(C\Image\Factory::class),
            $this->createMock(C\Panel\Factory::class),
            $this->createMock(C\Modal\Factory::class),
            $this->createMock(C\Dropzone\Factory::class),
            $this->createMock(C\Popover\Factory::class),
            $this->createMock(C\Divider\Factory::class),
            $this->createMock(C\Link\Factory::class),
            $this->createMock(C\Dropdown\Factory::class),
            $this->createMock(C\Item\Factory::class),
            $this->createMock(C\Icon\Factory::class),
            $this->createMock(C\ViewControl\Factory::class),
            $this->createMock(C\Chart\Factory::class),
            $this->createMock(C\Input\Factory::class),
            $this->createMock(C\Table\Factory::class),
            $this->createMock(C\MessageBox\Factory::class),
            $this->createMock(C\Card\Factory::class)
        );
    }

    public function test_implements_factory_interface()
    {
        $f = $this->getFactory();
        $c = $f->Breadcrumbs(array());

        $this->assertInstanceOf("ILIAS\\UI\\Factory", $f);
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\Breadcrumbs\\Breadcrumbs",
            $f->Breadcrumbs(array())
        );
    }

    public function testCrumbs()
    {
        $f = $this->getFactory();
        $crumbs = array(
            new I\Component\Link\Standard("label", '#'),
            new I\Component\Link\Standard("label2", '#')
        );

        $c = $f->Breadcrumbs($crumbs);
        $this->assertEquals($crumbs, $c->getItems());
    }

    public function testAppending()
    {
        $f = $this->getFactory();
        $crumb = new I\Component\Link\Standard("label2", '#');

        $c = $f->Breadcrumbs(array())
            ->withAppendedItem($crumb);
        $this->assertEquals(array($crumb), $c->getItems());
    }

    public function testRendering()
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $crumbs = array(
            new I\Component\Link\Standard("label", '#'),
            new I\Component\Link\Standard("label2", '#')
        );
        $c = $f->Breadcrumbs($crumbs);

        $html = $this->normalizeHTML($r->render($c));
        $expected = '<nav role="navigation" aria-label="breadcrumbs">'
            . '	<ul class="breadcrumb">'
            . '		<li class="crumb">'
            . '			<a href="#">label</a>'
            . '		</li>'
            . '		<li class="crumb">'
            . '			<a href="#">label2</a>'
            . '		</li>'
            . '	</ul>'
            . '</nav>';

        $this->assertHTMLEquals($expected, $html);
    }
}
