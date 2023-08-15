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
class PCListTest extends COPageTestBase
{
    public function testConstruction(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCList($page);
        $this->assertEquals(
            ilPCList::class,
            get_class($pc)
        );
    }

    public function testCreate(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCList($page);
        $pc->create($page, "pg");
        $this->assertXmlEquals(
            '<PageObject HierId="pg"><PageContent><List></List></PageContent></PageObject>',
            $page->getXMLFromDom()
        );
    }

    public function testAddItems(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCList($page);
        $pc->create($page, "pg");
        $pc->addItems(2);

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><List><ListItem/><ListItem/></List></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testListType(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCList($page);
        $pc->create($page, "pg");
        $pc->setListType("Ordered");

        $this->assertEquals(
            "Ordered",
            $pc->getListType()
        );

        $pc->setListType("Unordered");

        $this->assertEquals(
            "Unordered",
            $pc->getListType()
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><List Type="Unordered"/></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testNumberingType(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCList($page);
        $pc->create($page, "pg");
        $pc->setListType("Ordered");
        $pc->setNumberingType("Roman");

        $this->assertEquals(
            "Roman",
            $pc->getNumberingType()
        );

        $pc->setNumberingType("alphabetic");

        $this->assertEquals(
            "alphabetic",
            $pc->getNumberingType()
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><List Type="Ordered" NumberingType="alphabetic"/></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testStartValue(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCList($page);
        $pc->create($page, "pg");
        $pc->setListType("Ordered");
        $pc->setStartValue("3");

        $this->assertEquals(
            "3",
            $pc->getStartValue()
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><List Type="Ordered" StartValue="3"/></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testStyleClass(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCList($page);
        $pc->create($page, "pg");
        $pc->setListType("Ordered");
        $pc->setStyleClass("MyClass");

        $this->assertEquals(
            "MyClass",
            $pc->getStyleClass()
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><List Type="Ordered" Class="MyClass"/></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    //
    // Test file items
    //

    protected function getPageWithList(): ilPageObject
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCList($page);
        $pc->create($page, "pg");
        $pc->addItems(3);
        $page->addHierIDs();

        $par1 = new ilPCParagraph($page);
        $par1->create($page, "1_1");
        $par1->setLanguage("en");
        $par1->setText("One");

        $par2 = new ilPCParagraph($page);
        $par2->create($page, "1_2");
        $par2->setLanguage("en");
        $par2->setText("Two");

        $page->addHierIDs();
        $page->insertPCIds();

        return $page;
    }

    protected function getItemForHierId(ilPageObject $page, string $hier_id): ilPCListItem
    {
        $pc_id = $page->getPCIdForHierId($hier_id);
        $cont_node = $page->getContentDomNode($hier_id);
        $pc = new ilPCListItem($page);
        $pc->setDomNode($cont_node);
        $pc->setHierId($hier_id);
        $pc->setPcId($pc_id);
        return $pc;
    }

    public function testNewItemAfter(): void
    {
        $page = $this->getPageWithList();
        $item = $this->getItemForHierId($page, "1_1");
        $item->newItemAfter();
        $page->stripHierIDs();
        $page->stripPCIDs();

        $expected = <<<EOT
<PageObject><PageContent><List><ListItem><PageContent><Paragraph Language="en">One</Paragraph></PageContent></ListItem><ListItem/><ListItem><PageContent><Paragraph Language="en">Two</Paragraph></PageContent></ListItem><ListItem/></List></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testNewItemBefore(): void
    {
        $page = $this->getPageWithList();
        $item = $this->getItemForHierId($page, "1_2");
        $item->newItemBefore();
        $page->stripHierIDs();
        $page->stripPCIDs();

        $expected = <<<EOT
<PageObject><PageContent><List><ListItem><PageContent><Paragraph Language="en">One</Paragraph></PageContent></ListItem><ListItem/><ListItem><PageContent><Paragraph Language="en">Two</Paragraph></PageContent></ListItem><ListItem/></List></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testDeleteItem(): void
    {
        $page = $this->getPageWithList();
        $item = $this->getItemForHierId($page, "1_1");
        $item->deleteItem();
        $page->stripHierIDs();
        $page->stripPCIDs();

        $expected = <<<EOT
<PageObject><PageContent><List><ListItem><PageContent><Paragraph Language="en">Two</Paragraph></PageContent></ListItem><ListItem/></List></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testMoveItemDown(): void
    {
        $page = $this->getPageWithList();
        $item = $this->getItemForHierId($page, "1_1");
        $item->moveItemDown();
        $page->stripHierIDs();
        $page->stripPCIDs();

        $expected = <<<EOT
<PageObject><PageContent><List><ListItem><PageContent><Paragraph Language="en">Two</Paragraph></PageContent></ListItem><ListItem><PageContent><Paragraph Language="en">One</Paragraph></PageContent></ListItem><ListItem/></List></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testMoveItemUp(): void
    {
        $page = $this->getPageWithList();
        $item = $this->getItemForHierId($page, "1_2");
        $item->moveItemUp();
        $page->stripHierIDs();
        $page->stripPCIDs();

        $expected = <<<EOT
<PageObject><PageContent><List><ListItem><PageContent><Paragraph Language="en">Two</Paragraph></PageContent></ListItem><ListItem><PageContent><Paragraph Language="en">One</Paragraph></PageContent></ListItem><ListItem/></List></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }
}
