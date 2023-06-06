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

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PCTabsTest extends COPageTestBase
{
    public function testConstruction(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCTabs($page);
        $this->assertEquals(
            ilPCTabs::class,
            get_class($pc)
        );
    }

    public function testCreate(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCTabs($page);
        $pc->create($page, "pg", "");
        $this->assertXmlEquals(
            '<PageObject HierId="pg"><PageContent><Tabs></Tabs></PageContent></PageObject>',
            $page->getXMLFromDom()
        );
    }

    public function testType(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCTabs($page);
        $pc->create($page, "pg", "");
        $pc->setTabType("Carousel");

        $this->assertEquals(
            "Carousel",
            $pc->getTabType()
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><Tabs Type="Carousel"></Tabs></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testContentWidth(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCTabs($page);
        $pc->create($page, "pg", "");
        $pc->setContentWidth("100");

        $this->assertEquals(
            "100",
            $pc->getContentWidth()
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><Tabs ContentWidth="100"></Tabs></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testContentHeight(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCTabs($page);
        $pc->create($page, "pg", "");
        $pc->setContentHeight("200");

        $this->assertEquals(
            "200",
            $pc->getContentHeight()
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><Tabs ContentHeight="200"></Tabs></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testHorizontalAlign(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCTabs($page);
        $pc->create($page, "pg", "");
        $pc->setHorizontalAlign("Right");

        $this->assertEquals(
            "Right",
            $pc->getHorizontalAlign()
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><Tabs HorizontalAlign="Right"></Tabs></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testBehaviour(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCTabs($page);
        $pc->create($page, "pg", "");
        $pc->setBehavior("AllClosed");

        $this->assertEquals(
            "AllClosed",
            $pc->getBehavior()
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><Tabs Behavior="AllClosed"></Tabs></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    protected function getTabsWithTabs(ilPageObject $page): ilPCTabs
    {
        $pc = new ilPCTabs($page);
        $pc->create($page, "pg", "");
        $pc->setTabType("HorizontalAccordion");
        $pc->addTab("One");
        $pc->addTab("Two");
        $pc->addTab("Three");
        $page->addHierIDs();
        return $pc;
    }

    public function testAddTab(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = $this->getTabsWithTabs($page);

        /*
        $this->assertEquals(
            "AllClosed",
            $pc->getBehavior()
        );*/

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><Tabs Type="HorizontalAccordion"><Tab><TabCaption>One</TabCaption></Tab><Tab><TabCaption>Two</TabCaption></Tab><Tab><TabCaption>Three</TabCaption></Tab></Tabs></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testCaptions(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = $this->getTabsWithTabs($page);
        $pc->saveCaptions(
            [
                "1_1:" => "New 1",
                "1_2:" => "New 2",
                "1_3:" => "New 3"
            ]
        );

        $this->assertEquals(
            [
                0 => [
                    "pos" => 0,
                    "caption" => "New 1",
                    "pc_id" => "",
                    "hier_id" => "1_1"
                ],
                1 => [
                    "pos" => 1,
                    "caption" => "New 2",
                    "pc_id" => "",
                    "hier_id" => "1_2"
                ],
                2 => [
                    "pos" => 2,
                    "caption" => "New 3",
                    "pc_id" => "",
                    "hier_id" => "1_3"
                ]
            ],
            $pc->getCaptions()
        );

        $this->assertEquals(
            "New 2",
            $pc->getCaption("1_2", "")
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><Tabs Type="HorizontalAccordion"><Tab><TabCaption>New 1</TabCaption></Tab><Tab><TabCaption>New 2</TabCaption></Tab><Tab><TabCaption>New 3</TabCaption></Tab></Tabs></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testPositions(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = $this->getTabsWithTabs($page);
        $pc->savePositions(
            [
                "1_1:" => 3,
                "1_2:" => 2,
                "1_3:" => 1
            ]
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><Tabs Type="HorizontalAccordion"><Tab><TabCaption>Three</TabCaption></Tab><Tab><TabCaption>Two</TabCaption></Tab><Tab><TabCaption>One</TabCaption></Tab></Tabs></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testDeleteTab(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = $this->getTabsWithTabs($page);
        $pc->deleteTab("1_2", "");

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><Tabs Type="HorizontalAccordion"><Tab><TabCaption>One</TabCaption></Tab><Tab><TabCaption>Three</TabCaption></Tab></Tabs></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testTemplate(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCTabs($page);
        $pc->create($page, "pg", "");
        $pc->setTemplate("MyTemplate");

        $this->assertEquals(
            "MyTemplate",
            $pc->getTemplate()
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><Tabs Template="MyTemplate"></Tabs></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testAutoTime(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCTabs($page);
        $pc->create($page, "pg", "");
        $pc->setAutoTime(20);

        $this->assertEquals(
            20,
            $pc->getAutoTime()
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><Tabs AutoAnimWait="20"></Tabs></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testRandomStart(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCTabs($page);
        $pc->create($page, "pg", "");

        $pc->setRandomStart(false);
        $this->assertEquals(
            false,
            $pc->getRandomStart()
        );

        $pc->setRandomStart(true);
        $this->assertEquals(
            true,
            $pc->getRandomStart()
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><Tabs RandomStart="1"></Tabs></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    //
    // PCTab tests
    //

    protected function getTabForHierId(ilPageObject $page, string $hier_id): ilPCTab
    {
        $page->insertPCIds();
        $pc_id = $page->getPCIdForHierId($hier_id);
        $cont_node = $page->getContentDomNode($hier_id);
        $pc = new ilPCTab($page);
        $pc->setDomNode($cont_node);
        $pc->setHierId($hier_id);
        $pc->setPcId($pc_id);
        return $pc;
    }

    public function testNewItemAfter(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = $this->getTabsWithTabs($page);
        $tab = $this->getTabForHierId($page, "1_1");
        $tab->newItemAfter();

        $page->stripHierIDs();
        $page->stripPCIDs();

        $expected = <<<EOT
<PageObject><PageContent><Tabs Type="HorizontalAccordion"><Tab><TabCaption>One</TabCaption></Tab><Tab></Tab><Tab><TabCaption>Two</TabCaption></Tab><Tab><TabCaption>Three</TabCaption></Tab></Tabs></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testNewItemBefore(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = $this->getTabsWithTabs($page);
        $tab = $this->getTabForHierId($page, "1_2");
        $tab->newItemBefore();

        $page->stripHierIDs();
        $page->stripPCIDs();

        $expected = <<<EOT
<PageObject><PageContent><Tabs Type="HorizontalAccordion"><Tab><TabCaption>One</TabCaption></Tab><Tab></Tab><Tab><TabCaption>Two</TabCaption></Tab><Tab><TabCaption>Three</TabCaption></Tab></Tabs></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testDeleteItem(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = $this->getTabsWithTabs($page);
        $tab = $this->getTabForHierId($page, "1_2");
        $tab->deleteItem();

        $page->stripHierIDs();
        $page->stripPCIDs();

        $expected = <<<EOT
<PageObject><PageContent><Tabs Type="HorizontalAccordion"><Tab><TabCaption>One</TabCaption></Tab><Tab><TabCaption>Three</TabCaption></Tab></Tabs></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testMoveItemDown(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = $this->getTabsWithTabs($page);
        $tab = $this->getTabForHierId($page, "1_1");
        $tab->moveItemDown();

        $page->stripHierIDs();
        $page->stripPCIDs();

        $expected = <<<EOT
<PageObject><PageContent><Tabs Type="HorizontalAccordion"><Tab><TabCaption>Two</TabCaption></Tab><Tab><TabCaption>One</TabCaption></Tab><Tab><TabCaption>Three</TabCaption></Tab></Tabs></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testMoveItemUp(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = $this->getTabsWithTabs($page);
        $tab = $this->getTabForHierId($page, "1_2");
        $tab->moveItemUp();

        $page->stripHierIDs();
        $page->stripPCIDs();

        $expected = <<<EOT
<PageObject><PageContent><Tabs Type="HorizontalAccordion"><Tab><TabCaption>Two</TabCaption></Tab><Tab><TabCaption>One</TabCaption></Tab><Tab><TabCaption>Three</TabCaption></Tab></Tabs></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }
}
