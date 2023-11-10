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
class PCDataTableTest extends COPageTestBase
{
    public function testConstruction(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCDataTable($page);
        $this->assertEquals(
            ilPCDataTable::class,
            get_class($pc)
        );
    }

    public function testCreate(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCDataTable($page);
        $pc->create($page, "pg");
        $this->assertXmlEquals(
            '<PageObject HierId="pg"><PageContent><Table Language="" DataTable="y"></Table></PageContent></PageObject>',
            $page->getXMLFromDom()
        );
    }

    public function testData(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCDataTable($page);
        $pc->create($page, "pg");
        $row1 = $pc->addRow();
        $pc->addCell($row1, "one", "en");
        $pc->addCell($row1, "two", "en");
        $row2 = $pc->addRow();
        $pc->addCell($row2, "three", "en");
        $pc->addCell($row2, "four", "en");

        $expected = <<<EOT
<PageObject HierId="pg"><PageContent><Table Language="" DataTable="y">
<TableRow>
<TableData>
<PageContent><Paragraph Language="en" Characteristic="TableContent">one</Paragraph></PageContent>
</TableData>
<TableData>
<PageContent><Paragraph Language="en" Characteristic="TableContent">two</Paragraph></PageContent>
</TableData></TableRow>
<TableRow><TableData>
<PageContent><Paragraph Language="en" Characteristic="TableContent">three</Paragraph></PageContent>
</TableData>
<TableData>
<PageContent><Paragraph Language="en" Characteristic="TableContent">four</Paragraph></PageContent>
</TableData></TableRow></Table></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );

        $page->addHierIDs();
        $pc->setHierId("1");

        $pc->setData([
            ["five", "six"],
            ["seven", "eight"]
        ]);

        $expected = <<<EOT
<PageObject HierId="pg"><PageContent HierId="1"><Table Language="" DataTable="y"><TableRow HierId="1_1"><TableData HierId="1_1_1"><PageContent HierId="1_1_1_1"><Paragraph Language="en" Characteristic="TableContent">five</Paragraph></PageContent></TableData><TableData HierId="1_1_2"><PageContent HierId="1_1_2_1"><Paragraph Language="en" Characteristic="TableContent">six</Paragraph></PageContent></TableData></TableRow><TableRow HierId="1_2"><TableData HierId="1_2_1"><PageContent HierId="1_2_1_1"><Paragraph Language="en" Characteristic="TableContent">seven</Paragraph></PageContent></TableData><TableData HierId="1_2_2"><PageContent HierId="1_2_2_1"><Paragraph Language="en" Characteristic="TableContent">eight</Paragraph></PageContent></TableData></TableRow></Table></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testEmptyCell(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCDataTable($page);
        $pc->create($page, "pg");
        $pc->setLanguage("en");
        $row1 = $pc->addRow();
        $pc->addCell($row1, "one", "en");
        $page->addHierIDs();
        $pc->setHierId("1");
        // note: this gives us the paragraph node in the cell
        $cell = $pc->getCellNode(0, 0);
        $pc->makeEmptyCell($cell->parentNode->parentNode);
        $page->stripHierIDs();
        $page->validateDom();

        $expected = <<<EOT
<PageObject><PageContent><Table Language="en" DataTable="y">
<TableRow>
<TableData>
<PageContent><Paragraph Characteristic="TableContent" Language="en"></Paragraph></PageContent>
</TableData>
</TableRow>
</Table></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testCellText(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCDataTable($page);
        $pc->create($page, "pg");
        $pc->setLanguage("en");
        $row1 = $pc->addRow();
        $pc->addCell($row1, "one", "en");
        $page->addHierIDs();
        $pc->setHierId("1");

        // note: this gives us the paragraph node in the cell
        $text = $pc->getCellText(0, 0);

        $this->assertEquals(
            "one",
            $text
        );
    }

    public function testAddRows(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCDataTable($page);
        $pc->create($page, "pg");
        $pc->setLanguage("en");
        $pc->addRows(1, 2);
        $page->validateDom();

        $expected = <<<EOT
<PageObject><PageContent><Table Language="en" DataTable="y"><TableRow><TableData></TableData><TableData></TableData></TableRow></Table></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testImportSpreadsheet(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCDataTable($page);
        $pc->create($page, "pg");
        $pc->setLanguage("en");
        $pc->importSpreadsheet("en", "one\ttwo\nthree\tfour");
        $page->validateDom();

        $expected = <<<EOT
<PageObject><PageContent><Table Language="en" DataTable="y"><TableRow><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">one</Paragraph></PageContent></TableData><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">two</Paragraph></PageContent></TableData></TableRow><TableRow><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">three</Paragraph></PageContent></TableData><TableData><PageContent><Paragraph Language="en" Characteristic="TableContent">four</Paragraph></PageContent></TableData></TableRow></Table></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testLanguage(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCDataTable($page);
        $pc->create($page, "pg");
        $pc->setLanguage("fr");
        $pc->addRows(1, 1);
        $page->validateDom();
        $this->assertEquals(
            "fr",
            $pc->getLanguage()
        );
    }

    public function testWidth(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCDataTable($page);
        $pc->create($page, "pg");
        $pc->setLanguage("en");
        $pc->addRows(1, 1);
        $page->validateDom();
        $pc->setWidth("200");
        $this->assertEquals(
            "200",
            $pc->getWidth()
        );
    }

    public function testAlign(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCDataTable($page);
        $pc->create($page, "pg");
        $pc->setLanguage("en");
        $pc->addRows(1, 1);
        $page->validateDom();
        $pc->setHorizontalAlign("Right");
        $this->assertEquals(
            "Right",
            $pc->getHorizontalAlign()
        );
    }

    public function testTDWidth(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCDataTable($page);
        $pc->create($page, "pg");
        $pc->setLanguage("en");
        $pc->addRows(1, 1);
        $page->addHierIDs();
        $pc->setTDWidth("1_1_1", "33");
        $this->assertEquals(
            [
                "1_1_1:" => "33"
            ],
            $pc->getAllCellWidths()
        );
    }

    public function testSpans(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCDataTable($page);
        $pc->create($page, "pg");
        $pc->setLanguage("en");
        $pc->addRows(1, 2);
        $page->addHierIDs();
        $pc->setTdSpans(
            ["1_1_1:" => "2", "1_1_2:" => "1"],
            ["1_1_1:" => "1", "1_1_2:" => "1"],
        );

        $spans = $pc->getAllCellSpans();

        $this->assertEquals(
            "2",
            $spans["1_1_1:"]["colspan"]
        );
        $this->assertEquals(
            "",
            $spans["1_1_1:"]["rowspan"]
        );
        $this->assertEquals(
            "",
            $spans["1_1_2:"]["colspan"]
        );
        $this->assertEquals(
            "",
            $spans["1_1_2:"]["rowspan"]
        );
    }

    public function testCellHidden(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCDataTable($page);
        $pc->create($page, "pg");
        $colspans = [
            0 => [0 => 2, 1 => 1]
        ];
        $rowspans = [
            0 => [0 => 1, 1 => 1]
        ];
        $h1 = $pc->checkCellHidden(
            $colspans,
            $rowspans,
            0,
            0,
        );
        $h2 = $pc->checkCellHidden(
            $colspans,
            $rowspans,
            1,
            0,
        );

        $this->assertEquals(
            false,
            $h1
        );
        $this->assertEquals(
            true,
            $h2
        );
    }

    public function testTDClass(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCDataTable($page);
        $pc->create($page, "pg");
        $pc->setLanguage("en");
        $pc->addRows(1, 1);
        $page->addHierIDs();
        $pc->setTDClass("1_1_1", "MyClass");
        $this->assertEquals(
            [
                "1_1_1:" => "MyClass"
            ],
            $pc->getAllCellClasses()
        );
    }

    public function testTDAlignment(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCDataTable($page);
        $pc->create($page, "pg");
        $pc->setLanguage("en");
        $pc->addRows(1, 1);
        $page->addHierIDs();
        $pc->setTDAlignment("1_1_1", "Right");
        $this->assertEquals(
            [
                "1_1_1:" => "Right"
            ],
            $pc->getAllCellAlignments()
        );
    }

    public function testCaption(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCDataTable($page);
        $pc->create($page, "pg");
        $pc->setLanguage("en");
        $pc->addRows(1, 1);
        $page->addHierIDs();
        $pc->setHierId("1");
        $pc->setCaption("Moin", "Top");
        $this->assertEquals(
            "Moin",
            $pc->getCaption()
        );
        $this->assertEquals(
            "Top",
            $pc->getCaptionAlign()
        );
    }

    public function testFirstRowStyle(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCDataTable($page);
        $pc->create($page, "pg");
        $pc->setLanguage("en");
        $pc->addRows(2, 2);
        $page->addHierIDs();
        $pc->setFirstRowStyle("MyClass");
        $this->assertEquals(
            [
                '1_1_1:' => 'MyClass',
                '1_1_2:' => 'MyClass',
                '1_2_1:' => '',
                '1_2_2:' => ''
            ],
            $pc->getAllCellClasses()
        );
    }

    public function testClass(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCDataTable($page);
        $pc->create($page, "pg");
        $pc->setLanguage("en");
        $pc->addRows(1, 1);
        $page->addHierIDs();
        $pc->setHierId("1");
        $pc->setClass("MyClass");
        $this->assertEquals(
            "MyClass",
            $pc->getClass()
        );
    }

    public function testTemplate(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCDataTable($page);
        $pc->create($page, "pg");
        $pc->setLanguage("en");
        $pc->addRows(1, 1);
        $page->addHierIDs();
        $pc->setHierId("1");
        $pc->setTemplate("MyTemplate");
        $this->assertEquals(
            "MyTemplate",
            $pc->getTemplate()
        );
    }

    public function testHeaderRows(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCDataTable($page);
        $pc->create($page, "pg");
        $pc->setHeaderRows(2);
        $this->assertEquals(
            2,
            $pc->getHeaderRows()
        );
    }

    public function testFooterRows(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCDataTable($page);
        $pc->create($page, "pg");
        $pc->setFooterRows(2);
        $this->assertEquals(
            2,
            $pc->getFooterRows()
        );
    }

    public function testHeaderCols(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCDataTable($page);
        $pc->create($page, "pg");
        $pc->setHeaderCols(2);
        $this->assertEquals(
            2,
            $pc->getHeaderCols()
        );
    }

    public function testFooterCols(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCDataTable($page);
        $pc->create($page, "pg");
        $pc->setFooterCols(2);
        $this->assertEquals(
            2,
            $pc->getFooterCols()
        );
    }

    public function testModel(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCDataTable($page);
        $pc->create($page, "pg");
        $pc->setLanguage("en");
        $pc->importSpreadsheet("en", "one\ttwo\nthree\tfour");
        $page->addHierIDs();
        $pc->setHierId("1");
        //$page->validateDom();

        $expected = new \stdClass();
        $expected->content = [
            0 => [
                0 => "one",
                1 => "two"
            ],
            1 => [
                0 => "three",
                1 => "four"
            ]
        ];
        $expected->characteristic = '';
        $expected->template = '';
        $expected->hasHeaderRows = false;

        $this->assertEquals(
            $expected,
            $pc->getModel()
        );
    }
}
