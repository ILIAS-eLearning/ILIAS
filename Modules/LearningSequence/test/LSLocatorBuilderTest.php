<?php

declare(strict_types=1);

/* Copyright (c) 2021 - Nils Haagen <nils.haagen@concepts-and-training.de> - Extended GPL, see LICENSE */

use ILIAS\KioskMode\ControlBuilder;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Implementation\Component\BreadCrumbs\Breadcrumbs;

require_once('IliasMocks.php');
require_once(__DIR__ . "/../../../tests/UI/Base.php");

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

class LSLocatorBuilderTest extends ILIAS_UI_TestBase
{
    use IliasMocks;

    protected LSLocatorBuilder $lb;

    public function stripHTML(string $html): string
    {
        $html = $this->normalizeHTML($html);
        return preg_replace('!\s+!', ' ', $html);
    }

    public function setUp(): void
    {
        $cb = $this->createMock(LSControlBuilder::class);
        $this->lb = new LSLocatorBuilder('cmd', $cb);
    }

    public function testConstruction(): void
    {
        $this->assertInstanceOf(LSLocatorBuilder::class, $this->lb);
    }

    public function testItemCreation(): void
    {
        $this->lb
            ->item('item 1', 1)
            ->item('item 2', 2)
            ->item('item 3', 3);

        $this->assertCount(3, $this->lb->getItems());
    }

    public function testItemStruct(): void
    {
        $this->lb
            ->item('item 1', 1)
            ->item('item 2', 2);

        $expected = [
            [	'label' => 'item 1',
                'command' => 'cmd',
                'parameter' => 1
            ],
            [	'label' => 'item 2',
                'command' => 'cmd',
                'parameter' => 2
            ]
        ];

        $this->assertEquals($expected, $this->lb->getItems());
    }

    public function testEnd(): void
    {
        $cb = $this->lb->end();
        $this->assertInstanceOf(ControlBuilder::class, $cb);
    }

    public function testGUI(): void
    {
        $data_factory = new DataFactory();
        $uri = $data_factory->uri('https://ilias.de/somepath');
        $url_builder = new LSUrlBuilder($uri);
        $ui_factory = $this->mockUIFactory();

        $items = $this->lb
            ->item('item 1', 1)
            ->getItems();

        $gui = new ilLSLocatorGUI($url_builder, $ui_factory);
        $out = $gui->withItems($items)->getComponent();

        $this->assertInstanceOf(Breadcrumbs::class, $out);

        $expected = $this->stripHTML(
            '<nav aria-label="breadcrumbs_aria_label" class="breadcrumb_wrapper"> ' .
            '	<div class="breadcrumb"> ' .
            '		<span class="crumb"> ' .
            '			<a href="https://ilias.de/somepath?lsocmd=cmd&lsov=1" >item 1</a>' .
            '		</span> ' .
            '	</div>' .
            '</nav>'
        );

        $renderer = $this->getDefaultRenderer();
        $html = $this->stripHTML($renderer->render($out));
        $this->assertEquals($expected, $html);
    }
}
