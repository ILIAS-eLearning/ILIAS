<?php

use PHPUnit\Framework\TestCase;
use ILIAS\KioskMode\ControlBuilder;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Implementation\Component\BreadCrumbs\Breadcrumbs;

require_once('IliasMocks.php');
require_once(__DIR__ . "/../../../tests/UI/Base.php");

class LSLocatorBuilderTest extends ILIAS_UI_TestBase
{
    use IliasMocks;


    public function stripHTML(string $html) : string
    {
        $html = $this->normalizeHTML($html);
        return preg_replace('!\s+!', ' ', $html);
    }

    public function setUp()
    {
        $cb = $this->createMock(LSControlBuilder::class);
        $this->lb = new LSLocatorBuilder('cmd', $cb);
    }

    public function testConstruction()
    {
        $this->assertInstanceOf(LSLocatorBuilder::class, $this->lb);
    }

    public function testItemCreation()
    {
        $this->lb
            ->item('item 1', 1)
            ->item('item 2', 2)
            ->item('item 3', 3);

        $this->assertCount(3, $this->lb->getItems());
    }

    public function testItemStruct()
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

    public function testEnd()
    {
        $cb = $this->lb->end();
        $this->assertInstanceOf(ControlBuilder::class, $cb);
    }

    public function testGUI()
    {
        $data_factory = new DataFactory();
        $uri = $data_factory->uri('http://ilias.de/somepath');
        $url_builder = new LSUrlBuilder($uri);
        $ui_factory = $this->mockUIFactory();

        $items = $this->lb
            ->item('item 1', 1)
            ->getItems();

        $gui = new ilLSLocatorGUI($url_builder, $ui_factory);
        $out = $gui->withItems($items)->getComponent();

        $this->assertInstanceOf(Breadcrumbs::class, $out);

        $expected = $this->stripHTML(
            '<nav role="navigation" aria-label="breadcrumbs"> ' .
            '	<ul class="breadcrumb"> ' .
            '		<li class="crumb"> ' .
            '			<a href="http://ilias.de/somepath?lsocmd=cmd&lsov=1" >item 1</a>' .
            '		</li> ' .
            '	</ul>' .
            '</nav>'
        );

        $renderer = $this->getDefaultRenderer();
        $html = $this->stripHTML($renderer->render($out));
        $this->assertEquals($expected, $html);
    }
}
