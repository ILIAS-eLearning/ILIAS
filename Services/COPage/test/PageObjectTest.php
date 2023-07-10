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
class PageObjectTest extends COPageTestBase
{
    public function testConstruction(): void
    {
        $page = new ilUnitTestPageObject(0);
        $this->assertEquals(
            ilUnitTestPageObject::class,
            get_class($page)
        );
    }

    public function testSetXMLContent(): void
    {
        $page = new ilUnitTestPageObject(0);

        $page->setXMLContent("<PageObject></PageObject>");
        $this->assertEquals(
            "<PageObject></PageObject>",
            $page->getXMLContent()
        );
    }

    public function testGetXMLFromDom(): void
    {
        $page = new ilUnitTestPageObject(0);

        $page->setXMLContent("<PageObject></PageObject>");
        $page->buildDom();
        $this->assertXmlEquals(
            "<PageObject></PageObject>",
            $page->getXMLFromDom()
        );
    }

    public function testAddHierIds(): void
    {
        $page = new ilUnitTestPageObject(0);

        $page->setXMLContent("<PageObject></PageObject>");
        $page->buildDom();
        $page->addHierIDs();
        $this->assertXmlEquals(
            '<PageObject HierId="pg"></PageObject>',
            $page->getXMLFromDom()
        );
    }

    public function testAddHierIdsWithContent(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCGrid($page);
        $pc->create($page, "pg");
        $pc->applyTemplate(
            ilPCGridGUI::TEMPLATE_TWO_COLUMN,
            0,
            0,
            0,
            0,
            0
        );

        $page->addHierIDs();

        $pc = new ilPCDataTable($page);
        $pc->create($page, "1_2");
        $row1 = $pc->addRow();
        $pc->addCell($row1, "one", "en");
        $pc->addCell($row1, "two", "en");
        $row2 = $pc->addRow();
        $pc->addCell($row2, "three", "en");
        $pc->addCell($row2, "four", "en");

        $page->addHierIDs();

        $expected = <<<EOT
<PageObject HierId="pg"><PageContent HierId="1"><Grid><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="6" WIDTH_L="6" WIDTH_XL="6" HierId="1_1"/><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="6" WIDTH_L="6" WIDTH_XL="6" HierId="1_2"><PageContent HierId="1_2_1"><Table Language="" DataTable="y"><TableRow HierId="1_2_1_1"><TableData HierId="1_2_1_1_1"><PageContent HierId="1_2_1_1_1_1"><Paragraph Language="en" Characteristic="TableContent">one</Paragraph></PageContent></TableData><TableData HierId="1_2_1_1_2"><PageContent HierId="1_2_1_1_2_1"><Paragraph Language="en" Characteristic="TableContent">two</Paragraph></PageContent></TableData></TableRow><TableRow HierId="1_2_1_2"><TableData HierId="1_2_1_2_1"><PageContent HierId="1_2_1_2_1_1"><Paragraph Language="en" Characteristic="TableContent">three</Paragraph></PageContent></TableData><TableData HierId="1_2_1_2_2"><PageContent HierId="1_2_1_2_2_1"><Paragraph Language="en" Characteristic="TableContent">four</Paragraph></PageContent></TableData></TableRow></Table></PageContent></GridCell></Grid></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testGeneratePCId(): void
    {
        $page = $this->getEmptyPageWithDom();
        $id = $page->generatePCId();
        $this->assertEquals(
            32,
            strlen($id)
        );
    }
}
