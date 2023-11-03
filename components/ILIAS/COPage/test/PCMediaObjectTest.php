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

use PHPUnit\Framework\TestCase;

require_once("./components/ILIAS/MediaObjects/ImageMap/class.ilMapArea.php");

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PCMediaObjectTest extends COPageTestBase
{
    /**
     * @return (\ILIAS\Repository\Object\ObjectAdapter&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getObjectAdapterMock()
    {
        $object_adapter = $this->getMockBuilder(\ILIAS\Repository\Object\ObjectAdapter::class)
                             ->disableOriginalConstructor()
                             ->getMock();
        $object_adapter->method("getTypeForObjId")
                     ->willReturnCallback(fn() => "dummy");
        return $object_adapter;
    }

    public function testConstruction(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCMediaObject($page);
        $this->assertEquals(
            ilPCMediaObject::class,
            get_class($pc)
        );
    }

    public function testCreate(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCMediaObject($page);
        $pc->create($page, "pg");
        $this->assertXmlEquals(
            '<PageObject HierId="pg"></PageObject>',
            $page->getXMLFromDom()
        );
    }

    public function testCreateAlias(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCMediaObject($page);
        $pc->setMediaObject($this->getMediaObjectMock());
        $pc->createAlias($page, "pg");

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><MediaObject><MediaAlias OriginId="il__mob_0"></MediaAlias><MediaAliasItem Purpose="Standard"><Layout HorizontalAlign="Left"></Layout></MediaAliasItem><MediaAliasItem Purpose="Fullscreen"><Layout Width="100" Height="50"></Layout></MediaAliasItem></MediaObject></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    protected function getMediaObjectInPage(ilPageObject $page): ilPCMediaObject
    {
        $pc = new ilPCMediaObject($page);
        $pc->setMediaObject($this->getMediaObjectMock());
        $pc->createAlias($page, "pg");
        $page->addHierIDs();
        $page->insertPCIds();
        $pc->setHierId("1");
        $pc->setPCId($page->getPCIdForHierId("1"));
        $pc->setDomNode($pc->getDomNode());
        return $pc;
    }

    public function testDump(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = $this->getMediaObjectInPage($page);

        $page->stripHierIDs();
        $page->stripPCIDs();

        $expected = <<<EOT
<PageContent><MediaObject><MediaAlias OriginId="il__mob_0"/><MediaAliasItem Purpose="Standard"><Layout HorizontalAlign="Left"/></MediaAliasItem><MediaAliasItem Purpose="Fullscreen"><Layout Width="100" Height="50"/></MediaAliasItem></MediaObject></PageContent>
EOT;

        $this->assertXmlEquals(
            $expected,
            $pc->dumpXML()
        );
    }

    public function testSetClass(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = $this->getMediaObjectInPage($page);

        $pc->setClass("MyClass");

        $this->assertEquals(
            "MyClass",
            $pc->getClass()
        );

        $page->stripHierIDs();
        $page->stripPCIDs();

        $expected = <<<EOT
<PageObject><PageContent><MediaObject><MediaAlias OriginId="il__mob_0" Class="MyClass"/><MediaAliasItem Purpose="Standard"><Layout HorizontalAlign="Left"/></MediaAliasItem><MediaAliasItem Purpose="Fullscreen"><Layout Width="100" Height="50"/></MediaAliasItem></MediaObject></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testCaptionClass(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = $this->getMediaObjectInPage($page);

        $pc->setCaptionClass("MyCaptionClass");

        $this->assertEquals(
            "MyCaptionClass",
            $pc->getCaptionClass()
        );

        $page->stripHierIDs();
        $page->stripPCIDs();

        $expected = <<<EOT
<PageObject><PageContent><MediaObject><MediaAlias OriginId="il__mob_0" CaptionClass="MyCaptionClass"/><MediaAliasItem Purpose="Standard"><Layout HorizontalAlign="Left"/></MediaAliasItem><MediaAliasItem Purpose="Fullscreen"><Layout Width="100" Height="50"/></MediaAliasItem></MediaObject></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    // Media alias item tests

    public function checkMAProps(
        Closure $assert,
        ?string $expected = null
    ): void {
        $page = $this->getEmptyPageWithDom();
        $pc = $this->getMediaObjectInPage($page);
        $ma = $pc->getStandardMediaAliasItem();

        $assert($ma);

        $page->stripHierIDs();
        $page->stripPCIDs();

        if (!is_null($expected)) {
            $this->assertXmlEquals(
                $expected,
                $page->getXMLFromDom()
            );
        }
    }

    public function testWidth(): void
    {
        $this->checkMAProps(
            function (ilMediaAliasItem $ma): void {
                $this->assertEquals(
                    false,
                    $ma->definesSize()
                );
                $ma->setWidth("222");
                $this->assertEquals(
                    "222",
                    $ma->getWidth()
                );
                $this->assertEquals(
                    true,
                    $ma->definesSize()
                );
            },
            $expected = <<<EOT
<PageObject><PageContent><MediaObject><MediaAlias OriginId="il__mob_0"/><MediaAliasItem Purpose="Standard"><Layout HorizontalAlign="Left" Width="222"/></MediaAliasItem><MediaAliasItem Purpose="Fullscreen"><Layout Width="100" Height="50"/></MediaAliasItem></MediaObject></PageContent></PageObject>
EOT
        );
    }

    public function testHeight(): void
    {
        $this->checkMAProps(
            function (ilMediaAliasItem $ma): void {
                $ma->setHeight("11");
                $this->assertEquals(
                    "11",
                    $ma->getHeight()
                );
            },
            $expected = <<<EOT
<PageObject><PageContent><MediaObject><MediaAlias OriginId="il__mob_0"/><MediaAliasItem Purpose="Standard"><Layout HorizontalAlign="Left" Height="11"/></MediaAliasItem><MediaAliasItem Purpose="Fullscreen"><Layout Width="100" Height="50"/></MediaAliasItem></MediaObject></PageContent></PageObject>
EOT
        );
    }

    public function testDeriveSize(): void
    {
        $this->checkMAProps(
            function (ilMediaAliasItem $ma): void {
                $this->assertEquals(
                    false,
                    $ma->definesSize()
                );
                $ma->setWidth("222");
                $ma->setHeight("111");
                $this->assertEquals(
                    true,
                    $ma->definesSize()
                );
                $ma->deriveSize();
                $this->assertEquals(
                    false,
                    $ma->definesSize()
                );
            },
            $expected = <<<EOT
<PageObject><PageContent><MediaObject><MediaAlias OriginId="il__mob_0"/><MediaAliasItem Purpose="Standard"><Layout HorizontalAlign="Left"/></MediaAliasItem><MediaAliasItem Purpose="Fullscreen"><Layout Width="100" Height="50"/></MediaAliasItem></MediaObject></PageContent></PageObject>
EOT
        );
    }

    public function testCaption(): void
    {
        $this->checkMAProps(
            function (ilMediaAliasItem $ma): void {
                $this->assertEquals(
                    false,
                    $ma->definesCaption()
                );
                $ma->setCaption("My Caption");
                $this->assertEquals(
                    "My Caption",
                    $ma->getCaption()
                );
                $this->assertEquals(
                    true,
                    $ma->definesCaption()
                );
            },
            $expected = <<<EOT
<PageObject><PageContent><MediaObject><MediaAlias OriginId="il__mob_0"></MediaAlias><MediaAliasItem Purpose="Standard"><Layout HorizontalAlign="Left"></Layout><Caption Align="bottom">My Caption</Caption></MediaAliasItem><MediaAliasItem Purpose="Fullscreen"><Layout Width="100" Height="50"></Layout></MediaAliasItem></MediaObject></PageContent></PageObject>
EOT
        );
    }

    public function testDeriveCaption(): void
    {
        $this->checkMAProps(
            function (ilMediaAliasItem $ma): void {
                $ma->setCaption("My Caption");
                $this->assertEquals(
                    true,
                    $ma->definesCaption()
                );
                $ma->deriveCaption();
                $this->assertEquals(
                    false,
                    $ma->definesCaption()
                );
            },
            $expected = <<<EOT
<PageObject><PageContent><MediaObject><MediaAlias OriginId="il__mob_0"></MediaAlias><MediaAliasItem Purpose="Standard"><Layout HorizontalAlign="Left"></Layout></MediaAliasItem><MediaAliasItem Purpose="Fullscreen"><Layout Width="100" Height="50"></Layout></MediaAliasItem></MediaObject></PageContent></PageObject>
EOT
        );
    }

    public function testTextRepresentation(): void
    {
        $this->checkMAProps(
            function (ilMediaAliasItem $ma): void {
                $this->assertEquals(
                    false,
                    $ma->definesTextRepresentation()
                );
                $ma->setTextRepresentation("My Text");
                $this->assertEquals(
                    "My Text",
                    $ma->getTextRepresentation()
                );
                $this->assertEquals(
                    true,
                    $ma->definesTextRepresentation()
                );
            },
            $expected = <<<EOT
<PageObject><PageContent><MediaObject><MediaAlias OriginId="il__mob_0"></MediaAlias><MediaAliasItem Purpose="Standard"><Layout HorizontalAlign="Left"></Layout><TextRepresentation>My Text</TextRepresentation></MediaAliasItem><MediaAliasItem Purpose="Fullscreen"><Layout Width="100" Height="50"></Layout></MediaAliasItem></MediaObject></PageContent></PageObject>
EOT
        );
    }

    public function testDeriveTextRepresentation(): void
    {
        $this->checkMAProps(
            function (ilMediaAliasItem $ma): void {
                $ma->setTextRepresentation("My Text");
                $this->assertEquals(
                    true,
                    $ma->definesTextRepresentation()
                );
                $ma->deriveTextRepresentation();
                $this->assertEquals(
                    false,
                    $ma->definesTextRepresentation()
                );
            },
            $expected = <<<EOT
<PageObject><PageContent><MediaObject><MediaAlias OriginId="il__mob_0"></MediaAlias><MediaAliasItem Purpose="Standard"><Layout HorizontalAlign="Left"></Layout></MediaAliasItem><MediaAliasItem Purpose="Fullscreen"><Layout Width="100" Height="50"></Layout></MediaAliasItem></MediaObject></PageContent></PageObject>
EOT
        );
    }

    public function testHorizontalAlign(): void
    {
        $this->checkMAProps(
            function (ilMediaAliasItem $ma): void {
                $ma->setHorizontalAlign("Right");
                $this->assertEquals(
                    "Right",
                    $ma->getHorizontalAlign()
                );
            },
            $expected = <<<EOT
<PageObject><PageContent><MediaObject><MediaAlias OriginId="il__mob_0"></MediaAlias><MediaAliasItem Purpose="Standard"><Layout HorizontalAlign="Right"></Layout></MediaAliasItem><MediaAliasItem Purpose="Fullscreen"><Layout Width="100" Height="50"></Layout></MediaAliasItem></MediaObject></PageContent></PageObject>
EOT
        );
    }

    public function testParameters(): void
    {
        $this->checkMAProps(
            function (ilMediaAliasItem $ma): void {
                $this->assertEquals(
                    false,
                    $ma->definesParameters()
                );
                $ma->setParameters([
                    "par1" => "val1",
                    "par2" => "val2"
                ]);
                $this->assertEquals(
                    [
                        "par1" => "val1",
                        "par2" => "val2"
                    ],
                    $ma->getParameters()
                );
                $this->assertEquals(
                    true,
                    $ma->definesParameters()
                );
                $this->assertEquals(
                    'par1="val1", par2="val2"',
                    $ma->getParameterString()
                );
            },
            $expected = <<<EOT
<PageObject><PageContent><MediaObject><MediaAlias OriginId="il__mob_0"></MediaAlias><MediaAliasItem Purpose="Standard"><Layout HorizontalAlign="Left"></Layout><Parameter Name="par1" Value="val1"></Parameter><Parameter Name="par2" Value="val2"></Parameter></MediaAliasItem><MediaAliasItem Purpose="Fullscreen"><Layout Width="100" Height="50"></Layout></MediaAliasItem></MediaObject></PageContent></PageObject>
EOT
        );
    }

    public function testDeriveParameters(): void
    {
        $this->checkMAProps(
            function (ilMediaAliasItem $ma): void {
                $ma->setParameters([
                    "par1" => "val1",
                    "par2" => "val2"
                ]);
                $ma->deriveParameters();
                $this->assertEquals(
                    false,
                    $ma->definesParameters()
                );
            },
            $expected = <<<EOT
<PageObject><PageContent><MediaObject><MediaAlias OriginId="il__mob_0"></MediaAlias><MediaAliasItem Purpose="Standard"><Layout HorizontalAlign="Left"></Layout></MediaAliasItem><MediaAliasItem Purpose="Fullscreen"><Layout Width="100" Height="50"></Layout></MediaAliasItem></MediaObject></PageContent></PageObject>
EOT
        );
    }

    public function testAddMapArea(): void
    {
        $this->checkMAProps(
            function (ilMediaAliasItem $ma): void {
                $ma->addMapArea(
                    IL_AREA_RECT,
                    "10,10,100,100",
                    "Area Title",
                    [
                        "LinkType" => "ext",
                        "Href" => "http://www.ilias.de"
                    ],
                    "One"
                );
                $this->assertEquals(
                    [
                        0 => [
                            "Nr" => 1,
                            "Shape" => "Rect",
                            "Coords" => "10,10,100,100",
                            "HighlightMode" => "",
                            "HighlightClass" => "",
                            "Id" => "One",
                            "Link" => [
                                "LinkType" => "ExtLink",
                                "Href" => "http://www.ilias.de",
                                "Title" => "Area Title"
                            ]
                        ]
                    ],
                    $ma->getMapAreas()
                );
                $this->assertEquals(
                    "http://www.ilias.de",
                    $ma->getHrefOfArea(1)
                );
            },
            $expected = <<<EOT
<PageObject><PageContent><MediaObject><MediaAlias OriginId="il__mob_0"></MediaAlias><MediaAliasItem Purpose="Standard"><Layout HorizontalAlign="Left"></Layout><MapArea Shape="Rect" Coords="10,10,100,100" Id="One"><ExtLink Href="http://www.ilias.de">Area Title</ExtLink></MapArea></MediaAliasItem><MediaAliasItem Purpose="Fullscreen"><Layout Width="100" Height="50"></Layout></MediaAliasItem></MediaObject></PageContent></PageObject>
EOT
        );
    }

    public function testDeleteMapArea(): void
    {
        $this->checkMAProps(
            function (ilMediaAliasItem $ma): void {
                $ma->addMapArea(
                    IL_AREA_RECT,
                    "10,10,100,100",
                    "Area Title",
                    [
                        "LinkType" => "ext",
                        "Href" => "http://www.ilias.de"
                    ],
                    "One"
                );
                $ma->addMapArea(
                    IL_AREA_RECT,
                    "11,11,101,101",
                    "Area Title 2",
                    [
                        "LinkType" => "ext",
                        "Href" => "http://ilias.de"
                    ],
                    "Two"
                );
                $ma->deleteMapArea(1);
            },
            $expected = <<<EOT
<PageObject><PageContent><MediaObject><MediaAlias OriginId="il__mob_0"></MediaAlias><MediaAliasItem Purpose="Standard"><Layout HorizontalAlign="Left"></Layout><MapArea Shape="Rect" Coords="11,11,101,101" Id="Two"><ExtLink Href="http://ilias.de">Area Title 2</ExtLink></MapArea></MediaAliasItem><MediaAliasItem Purpose="Fullscreen"><Layout Width="100" Height="50"></Layout></MediaAliasItem></MediaObject></PageContent></PageObject>
EOT
        );
    }

    public function testDeleteMapAreaById(): void
    {
        $this->checkMAProps(
            function (ilMediaAliasItem $ma): void {
                $ma->addMapArea(
                    IL_AREA_RECT,
                    "10,10,100,100",
                    "Area Title",
                    [
                        "LinkType" => "ext",
                        "Href" => "http://www.ilias.de"
                    ],
                    "One"
                );
                $ma->addMapArea(
                    IL_AREA_RECT,
                    "11,11,101,101",
                    "Area Title 2",
                    [
                        "LinkType" => "ext",
                        "Href" => "http://ilias.de"
                    ],
                    "Two"
                );
                $ma->deleteMapAreaById("One");
            },
            $expected = <<<EOT
<PageObject><PageContent><MediaObject><MediaAlias OriginId="il__mob_0"></MediaAlias><MediaAliasItem Purpose="Standard"><Layout HorizontalAlign="Left"></Layout><MapArea Shape="Rect" Coords="11,11,101,101" Id="Two"><ExtLink Href="http://ilias.de">Area Title 2</ExtLink></MapArea></MediaAliasItem><MediaAliasItem Purpose="Fullscreen"><Layout Width="100" Height="50"></Layout></MediaAliasItem></MediaObject></PageContent></PageObject>
EOT
        );
    }

    public function testDeleteAllMapAreas(): void
    {
        $this->checkMAProps(
            function (ilMediaAliasItem $ma): void {
                $ma->addMapArea(
                    IL_AREA_RECT,
                    "10,10,100,100",
                    "Area Title",
                    [
                        "LinkType" => "ext",
                        "Href" => "http://www.ilias.de"
                    ],
                    "One"
                );
                $ma->addMapArea(
                    IL_AREA_RECT,
                    "11,11,101,101",
                    "Area Title 2",
                    [
                        "LinkType" => "ext",
                        "Href" => "http://ilias.de"
                    ],
                    "Two"
                );
                $ma->deleteAllMapAreas();
            },
            $expected = <<<EOT
<PageObject><PageContent><MediaObject><MediaAlias OriginId="il__mob_0"></MediaAlias><MediaAliasItem Purpose="Standard"><Layout HorizontalAlign="Left"></Layout></MediaAliasItem><MediaAliasItem Purpose="Fullscreen"><Layout Width="100" Height="50"></Layout></MediaAliasItem></MediaObject></PageContent></PageObject>
EOT
        );
    }

    public function testAreaProps(): void
    {
        $this->checkMAProps(
            function (ilMediaAliasItem $ma): void {
                $ma->addMapArea(
                    IL_AREA_RECT,
                    "10,10,100,100",
                    "Area Title",
                    [
                        "LinkType" => "int",
                        "Type" => "ltype",
                        "Target" => "ltarget",
                        "TargetFrame" => "ltargetframe"
                    ],
                    "One"
                );
                $this->assertEquals(
                    "int",
                    $ma->getLinkTypeOfArea(1)
                );
                $this->assertEquals(
                    "ltype",
                    $ma->getTypeOfArea(1)
                );
                $this->assertEquals(
                    "ltarget",
                    $ma->getTargetOfArea(1)
                );
                $this->assertEquals(
                    "ltargetframe",
                    $ma->getTargetFrameOfArea(1)
                );
                $this->assertEquals(
                    "Area Title",
                    $ma->getTitleOfArea(1)
                );
            },
            null
        );
    }
}
