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
class PCGridTest extends COPageTestBase
{
    public function testConstruction(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCGrid($page);
        $this->assertEquals(
            ilPCGrid::class,
            get_class($pc)
        );
    }

    public function testCreate(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCGrid($page);
        $pc->create($page, "pg");
        $this->assertXmlEquals(
            '<PageObject HierId="pg"><PageContent><Grid></Grid></PageContent></PageObject>',
            $page->getXMLFromDom()
        );
    }

    public function testApplyTemplateTwoColumn(): void
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

        $expected = <<<EOT
<PageObject HierId="pg"><PageContent><Grid><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="6" WIDTH_L="6" WIDTH_XL="6"/><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="6" WIDTH_L="6" WIDTH_XL="6"/></Grid></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testApplyTemplateManual(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCGrid($page);
        $pc->create($page, "pg");
        $pc->applyTemplate(
            ilPCGridGUI::TEMPLATE_MANUAL,
            4,
            12,
            6,
            3,
            3
        );

        $expected = <<<EOT
<PageObject HierId="pg"><PageContent><Grid><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="6" WIDTH_L="3" WIDTH_XL="3"/><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="6" WIDTH_L="3" WIDTH_XL="3"/><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="6" WIDTH_L="3" WIDTH_XL="3"/><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="6" WIDTH_L="3" WIDTH_XL="3"/></Grid></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testPositions(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCGrid($page);
        $pc->create($page, "pg");
        $pc->applyTemplate(
            ilPCGridGUI::TEMPLATE_MAIN_SIDE,
            0,
            0,
            0,
            0,
            0
        );
        $page->addHierIDs();
        $pc->savePositions([
            "1_1:" => 20,
            "1_2:" => 10
        ]);
        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><Grid><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="6" WIDTH_L="4" WIDTH_XL="3"/><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="6" WIDTH_L="8" WIDTH_XL="9"/></Grid></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testWidths(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCGrid($page);
        $pc->create($page, "pg");
        $pc->applyTemplate(
            ilPCGridGUI::TEMPLATE_MAIN_SIDE,
            0,
            0,
            0,
            0,
            0
        );
        $page->addHierIDs();
        $pc->saveWidths(
            [
            "1_1:" => 12,
            "1_2:" => 12
        ],
            [
            "1_1:" => 12,
            "1_2:" => 12
        ],
            [
            "1_1:" => 6,
            "1_2:" => 6
        ],
            [
            "1_1:" => 6,
            "1_2:" => 6
        ],
        );
        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><Grid><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="12" WIDTH_L="6" WIDTH_XL="6"/><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="12" WIDTH_L="6" WIDTH_XL="6"/></Grid></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testDelete(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCGrid($page);
        $pc->create($page, "pg");
        $pc->applyTemplate(
            ilPCGridGUI::TEMPLATE_MAIN_SIDE,
            0,
            0,
            0,
            0,
            0
        );
        $page->addHierIDs();
        $pc->deleteGridCell("1_2", "");
        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><Grid><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="6" WIDTH_L="8" WIDTH_XL="9"/></Grid></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testAddGridCell(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCGrid($page);
        $pc->create($page, "pg");
        $pc->applyTemplate(
            ilPCGridGUI::TEMPLATE_MANUAL,
            1,
            12,
            6,
            3,
            3
        );
        $pc->addGridCell(12, 12, 12, 6);

        $expected = <<<EOT
<PageObject HierId="pg"><PageContent><Grid><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="6" WIDTH_L="3" WIDTH_XL="3"/><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="12" WIDTH_L="12" WIDTH_XL="6"/></Grid></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testAddCell(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCGrid($page);
        $pc->create($page, "pg");
        $pc->applyTemplate(
            ilPCGridGUI::TEMPLATE_MANUAL,
            1,
            12,
            6,
            3,
            3
        );
        $pc->addCell();

        $expected = <<<EOT
<PageObject HierId="pg"><PageContent><Grid><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="6" WIDTH_L="3" WIDTH_XL="3"/><GridCell WIDTH_XS="" WIDTH_S="" WIDTH_M="" WIDTH_L="" WIDTH_XL=""/></Grid></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }


    public function testCellData(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCGrid($page);
        $pc->create($page, "pg");
        $pc->applyTemplate(
            ilPCGridGUI::TEMPLATE_MAIN_SIDE,
            0,
            0,
            0,
            0,
            0
        );
        $page->addHierIDs();

        $this->assertEquals(
            [
                0 => [
                    "xs" => "",
                    "s" => "12",
                    "m" => "6",
                    "l" => "8",
                    "xl" => "9",
                    "pc_id" => "",
                    "hier_id" => "1_1",
                    "pos" => 0
                ],
                1 => [
                    "xs" => "",
                    "s" => "12",
                    "m" => "6",
                    "l" => "4",
                    "xl" => "3",
                    "pc_id" => "",
                    "hier_id" => "1_2",
                    "pos" => 1
                ]
            ],
            $pc->getCellData()
        );
    }

    //
    // Test file items
    //

    protected function getPageWithGrid(): ilPageObject
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCGrid($page);
        $pc->create($page, "pg");
        $pc->applyTemplate(
            ilPCGridGUI::TEMPLATE_MAIN_SIDE,
            0,
            0,
            0,
            0,
            0
        );
        $page->addHierIDs();
        $page->insertPCIds();
        $pc->setHierId("1");
        return $page;
    }

    protected function getCellForHierId(ilPageObject $page, string $hier_id): ilPCGridCell
    {
        $pc_id = $page->getPCIdForHierId($hier_id);
        $cont_node = $page->getContentDomNode($hier_id);
        $pc = new ilPCGridCell($page);
        $pc->setDomNode($cont_node);
        $pc->setHierId($hier_id);
        $pc->setPcId($pc_id);
        return $pc;
    }

    public function testMoveRight(): void
    {
        $page = $this->getPageWithGrid();
        $cell = $this->getCellForHierId($page, "1_1");
        $cell->moveCellRight();
        $page->stripHierIDs();
        $page->stripPCIDs();

        $expected = <<<EOT
<PageObject><PageContent><Grid><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="6" WIDTH_L="4" WIDTH_XL="3"/><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="6" WIDTH_L="8" WIDTH_XL="9"/></Grid></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testMoveLeft(): void
    {
        $page = $this->getPageWithGrid();
        $cell = $this->getCellForHierId($page, "1_2");
        $cell->moveCellLeft();
        $page->stripHierIDs();
        $page->stripPCIDs();

        $expected = <<<EOT
<PageObject><PageContent><Grid><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="6" WIDTH_L="4" WIDTH_XL="3"/><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="6" WIDTH_L="8" WIDTH_XL="9"/></Grid></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }
}
