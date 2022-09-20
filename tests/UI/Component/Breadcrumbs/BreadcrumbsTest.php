<?php

declare(strict_types=1);

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
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation as I;

/**
 * Tests for the Breadcrumbs-component
 */
class BreadcrumbsTest extends ILIAS_UI_TestBase
{
    public function getFactory(): NoUIFactory
    {
        return new class () extends NoUIFactory {
            public function breadcrumbs(array $crumbs): C\Breadcrumbs\Breadcrumbs
            {
                return new I\Component\Breadcrumbs\Breadcrumbs($crumbs);
            }
        };
    }

    public function test_implements_factory_interface(): void
    {
        $f = $this->getFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Factory", $f);
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\Breadcrumbs\\Breadcrumbs",
            $f->breadcrumbs(array())
        );
    }

    public function testCrumbs(): void
    {
        $f = $this->getFactory();
        $crumbs = array(
            new I\Component\Link\Standard("label", '#'),
            new I\Component\Link\Standard("label2", '#')
        );

        $c = $f->breadcrumbs($crumbs);
        $this->assertEquals($crumbs, $c->getItems());
    }

    public function testAppending(): void
    {
        $f = $this->getFactory();
        $crumb = new I\Component\Link\Standard("label2", '#');

        $c = $f->Breadcrumbs(array())
            ->withAppendedItem($crumb);
        $this->assertEquals(array($crumb), $c->getItems());
    }

    public function testRendering(): void
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
