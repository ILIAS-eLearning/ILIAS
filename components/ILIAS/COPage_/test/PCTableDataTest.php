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
class PCTableDataTest extends COPageTestBase
{
    public function testConstruction(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCTableData($page);
        $this->assertEquals(
            ilPCTableData::class,
            get_class($pc)
        );
    }

    protected function getPageWithTable(): ilPageObject
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCDataTable($page);
        $pc->create($page, "pg");
        $pc->setLanguage("en");
        $pc->importSpreadsheet("en", "one\ttwo\nthree\tfour");
        $page->addHierIDs();
        $page->insertPCIds();
        $pc->setHierId("1");
        return $page;
    }

    protected function getTDForHierId(ilPageObject $page, string $hier_id): ilPCTableData
    {
        $pc_id = $page->getPCIdForHierId($hier_id);
        $cont_node = $page->getContentDomNode($hier_id);
        $pc = new ilPCTableData($page);
        $pc->setDomNode($cont_node);
        $pc->setHierId($hier_id);
        $pc->setPcId($pc_id);
        return $pc;
    }

    public function testNewRowAfter(): void
    {
        $page = $this->getPageWithTable();
        $td = $this->getTDForHierId($page, "1_1_1");
        $td->newRowAfter();
        $page->stripHierIDs();
        $page->stripPCIDs();

        $expected = <<<EOT
<PageObject><PageContent><Table Language="en" DataTable="y"><TableRow><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">one</Paragraph></PageContent></TableData><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">two</Paragraph></PageContent></TableData></TableRow><TableRow><TableData/><TableData/></TableRow><TableRow><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">three</Paragraph></PageContent></TableData><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">four</Paragraph></PageContent></TableData></TableRow></Table></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testNewRowBefore(): void
    {
        $page = $this->getPageWithTable();
        $td = $this->getTDForHierId($page, "1_2_1");
        $td->newRowBefore();
        $page->stripHierIDs();
        $page->stripPCIDs();

        $expected = <<<EOT
<PageObject><PageContent><Table Language="en" DataTable="y"><TableRow><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">one</Paragraph></PageContent></TableData><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">two</Paragraph></PageContent></TableData></TableRow><TableRow><TableData/><TableData/></TableRow><TableRow><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">three</Paragraph></PageContent></TableData><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">four</Paragraph></PageContent></TableData></TableRow></Table></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testDeleteRow(): void
    {
        $page = $this->getPageWithTable();
        $td = $this->getTDForHierId($page, "1_2_1");
        $td->deleteRow();
        $page->stripHierIDs();
        $page->stripPCIDs();

        $expected = <<<EOT
<PageObject><PageContent><Table Language="en" DataTable="y"><TableRow><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">one</Paragraph></PageContent></TableData><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">two</Paragraph></PageContent></TableData></TableRow></Table></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testNewColAfter(): void
    {
        $page = $this->getPageWithTable();
        $td = $this->getTDForHierId($page, "1_1_1");
        $td->newColAfter();
        $page->stripHierIDs();
        $page->stripPCIDs();

        $expected = <<<EOT
<PageObject><PageContent><Table Language="en" DataTable="y"><TableRow><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">one</Paragraph></PageContent></TableData><TableData/><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">two</Paragraph></PageContent></TableData></TableRow><TableRow><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">three</Paragraph></PageContent></TableData><TableData/><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">four</Paragraph></PageContent></TableData></TableRow></Table></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testNewColBefore(): void
    {
        $page = $this->getPageWithTable();
        $td = $this->getTDForHierId($page, "1_1_2");
        $td->newColBefore();
        $page->stripHierIDs();
        $page->stripPCIDs();

        $expected = <<<EOT
<PageObject><PageContent><Table Language="en" DataTable="y"><TableRow><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">one</Paragraph></PageContent></TableData><TableData/><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">two</Paragraph></PageContent></TableData></TableRow><TableRow><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">three</Paragraph></PageContent></TableData><TableData/><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">four</Paragraph></PageContent></TableData></TableRow></Table></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testDeleteCol(): void
    {
        $page = $this->getPageWithTable();
        $td = $this->getTDForHierId($page, "1_1_1");
        $td->deleteCol();
        $page->stripHierIDs();
        $page->stripPCIDs();

        $expected = <<<EOT
<PageObject><PageContent><Table Language="en" DataTable="y"><TableRow><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">two</Paragraph></PageContent></TableData></TableRow><TableRow><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">four</Paragraph></PageContent></TableData></TableRow></Table></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testMoveRowDown(): void
    {
        $page = $this->getPageWithTable();
        $td = $this->getTDForHierId($page, "1_1_1");
        $td->moveRowDown();
        $page->stripHierIDs();
        $page->stripPCIDs();

        $expected = <<<EOT
<PageObject><PageContent><Table Language="en" DataTable="y"><TableRow><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">three</Paragraph></PageContent></TableData><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">four</Paragraph></PageContent></TableData></TableRow><TableRow><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">one</Paragraph></PageContent></TableData><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">two</Paragraph></PageContent></TableData></TableRow></Table></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testMoveRowUp(): void
    {
        $page = $this->getPageWithTable();
        $td = $this->getTDForHierId($page, "1_2_1");
        $td->moveRowUp();
        $page->stripHierIDs();
        $page->stripPCIDs();

        $expected = <<<EOT
<PageObject><PageContent><Table Language="en" DataTable="y"><TableRow><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">three</Paragraph></PageContent></TableData><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">four</Paragraph></PageContent></TableData></TableRow><TableRow><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">one</Paragraph></PageContent></TableData><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">two</Paragraph></PageContent></TableData></TableRow></Table></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testMoveColRight(): void
    {
        $page = $this->getPageWithTable();
        $td = $this->getTDForHierId($page, "1_1_1");
        $td->moveColRight();
        $page->stripHierIDs();
        $page->stripPCIDs();

        $expected = <<<EOT
<PageObject><PageContent><Table Language="en" DataTable="y"><TableRow><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">two</Paragraph></PageContent></TableData><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">one</Paragraph></PageContent></TableData></TableRow><TableRow><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">four</Paragraph></PageContent></TableData><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">three</Paragraph></PageContent></TableData></TableRow></Table></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testMoveColLeft(): void
    {
        $page = $this->getPageWithTable();
        $td = $this->getTDForHierId($page, "1_1_2");
        $td->moveColLeft();
        $page->stripHierIDs();
        $page->stripPCIDs();

        $expected = <<<EOT
<PageObject><PageContent><Table Language="en" DataTable="y"><TableRow><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">two</Paragraph></PageContent></TableData><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">one</Paragraph></PageContent></TableData></TableRow><TableRow><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">four</Paragraph></PageContent></TableData><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">three</Paragraph></PageContent></TableData></TableRow></Table></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }
}
