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
}
