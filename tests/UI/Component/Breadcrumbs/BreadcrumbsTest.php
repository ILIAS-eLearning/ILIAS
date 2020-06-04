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
        return new class extends NoUIFactory {
            public function breadcrumbs(array $crumbs)
            {
                return new I\Component\Breadcrumbs\Breadcrumbs($crumbs);
            }
        };
    }

    public function test_implements_factory_interface()
    {
        $f = $this->getFactory();
        $c = $f->breadcrumbs(array());

        $this->assertInstanceOf("ILIAS\\UI\\Factory", $f);
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\Breadcrumbs\\Breadcrumbs",
            $f->breadcrumbs(array())
        );
    }

    public function testCrumbs()
    {
        $f = $this->getFactory();
        $crumbs = array(
            new I\Component\Link\Standard("label", '#'),
            new I\Component\Link\Standard("label2", '#')
        );

        $c = $f->breadcrumbs($crumbs);
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
        $expected = '<nav aria-label="breadcrumbs_aria_label" class="breadcrumb_wrapper">'
            . '	<div class="breadcrumb">'
            . '		<span class="crumb">'
            . '			<a href="#">label</a>'
            . '		</span>'
            . '		<span class="crumb">'
            . '			<a href="#">label2</a>'
            . '		</span>'
            . '	</div>'
            . '</nav>';

        $this->assertHTMLEquals($expected, $html);
    }
}
