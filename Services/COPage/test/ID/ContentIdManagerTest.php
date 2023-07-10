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

declare(strict_types=1);

namespace ILIAS\COPage\Test\ID;

use PHPUnit\Framework\TestCase;
use ILIAS\COPage\ID\ContentIdManager;
use ILIAS\COPage\ID\ContentIdGenerator;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ContentIdManagerTest extends \COPageTestBase
{
    protected int $pc_cnt;
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testInsertPCIds(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new \ilPCGrid($page);
        $pc->create($page, "pg");
        $pc->applyTemplate(
            \ilPCGridGUI::TEMPLATE_TWO_COLUMN,
            0,
            0,
            0,
            0,
            0
        );

        $id_manager = $this->getIDManager($page);
        $id_manager->insertPCIds();

        $expected = <<<EOT
<PageObject HierId="pg"><PageContent PCID="00000000000000000000000000000001"><Grid><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="6" WIDTH_L="6" WIDTH_XL="6" PCID="00000000000000000000000000000002"/><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="6" WIDTH_L="6" WIDTH_XL="6" PCID="00000000000000000000000000000003"/></Grid></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }


    public function testDuplicatePCIds(): void
    {
        $page = $this->getEmptyPageWithDom();
        $id_manager = $this->getIDManager($page);

        $this->insertParagraphAt($page, "pg");
        $id_manager->insertPCIds();

        $this->insertParagraphAt($page, "1");
        $id_manager->insertPCIds();

        $this->assertEquals(
            false,
            $id_manager->hasDuplicatePCIds()
        );

        $this->insertParagraphAt($page, "2");
        // force a duplicate
        $this->pc_cnt--;
        $id_manager->insertPCIds();

        $this->assertEquals(
            [0 => "00000000000000000000000000000002"],
            $id_manager->getDuplicatePCIds()
        );

        $this->assertEquals(
            true,
            $id_manager->hasDuplicatePCIds()
        );
    }

    public function testStripPCIds(): void
    {
        $page = $this->getEmptyPageWithDom();
        $id_manager = $this->getIDManager($page);

        $this->insertParagraphAt($page, "pg");
        $this->insertParagraphAt($page, "1");
        $id_manager->insertPCIds();

        $id_manager->stripPCIDs();

        $expected = <<<EOT
<PageObject HierId="pg"><PageContent HierId="1"><Paragraph Language="en"/></PageContent><PageContent HierId="2"><Paragraph Language="en"/></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testGetHierIdsForPCIds(): void
    {
        $page = $this->getEmptyPageWithDom();
        $id_manager = $this->getIDManager($page);

        $this->insertParagraphAt($page, "pg");
        $this->insertParagraphAt($page, "1");
        $this->insertParagraphAt($page, "2");
        $id_manager->insertPCIds();

        $hier_ids = $id_manager->getHierIdsForPCIds([
            "00000000000000000000000000000001", "00000000000000000000000000000002"
        ]);

        $this->assertEquals(
            [
                "00000000000000000000000000000001" => "1",
                "00000000000000000000000000000002" => "2"
            ],
            $hier_ids
        );
    }

    public function testGetHierIdForPCId(): void
    {
        $page = $this->getEmptyPageWithDom();
        $id_manager = $this->getIDManager($page);

        $this->insertParagraphAt($page, "pg");
        $this->insertParagraphAt($page, "1");
        $this->insertParagraphAt($page, "2");
        $id_manager->insertPCIds();

        $this->assertEquals(
            "2",
            $id_manager->getHierIdForPCId("00000000000000000000000000000002")
        );
    }

    public function testGetPCIdsForHierIds(): void
    {
        $page = $this->getEmptyPageWithDom();
        $id_manager = $this->getIDManager($page);

        $this->insertParagraphAt($page, "pg");
        $this->insertParagraphAt($page, "1");
        $this->insertParagraphAt($page, "2");
        $id_manager->insertPCIds();

        $hier_ids = $id_manager->getPCIdsForHierIds([
            "1", "2"
        ]);

        $this->assertEquals(
            [
                "1" => "00000000000000000000000000000001",
                "2" => "00000000000000000000000000000002"
            ],
            $hier_ids
        );
    }

    public function testGetPCIdForHierId(): void
    {
        $page = $this->getEmptyPageWithDom();
        $id_manager = $this->getIDManager($page);

        $this->insertParagraphAt($page, "pg");
        $this->insertParagraphAt($page, "1");
        $this->insertParagraphAt($page, "2");
        $id_manager->insertPCIds();

        $this->assertEquals(
            "00000000000000000000000000000002",
            $id_manager->getPCIdForHierId("2")
        );
    }

    public function testCheckPCIds(): void
    {
        $page = $this->getEmptyPageWithDom();
        $id_manager = $this->getIDManager($page);

        $this->insertParagraphAt($page, "pg");
        $this->insertParagraphAt($page, "1");
        $this->insertParagraphAt($page, "2");

        $this->assertEquals(
            false,
            $id_manager->checkPCIds()
        );

        $id_manager->insertPCIds();

        $this->assertEquals(
            true,
            $id_manager->checkPCIds()
        );
    }

    public function testGetAllPCIds(): void
    {
        $page = $this->getEmptyPageWithDom();
        $id_manager = $this->getIDManager($page);

        $this->insertParagraphAt($page, "pg");
        $this->insertParagraphAt($page, "1");
        $this->insertParagraphAt($page, "2");
        $id_manager->insertPCIds();

        $this->assertEquals(
            [
                "00000000000000000000000000000001",
                "00000000000000000000000000000002",
                "00000000000000000000000000000003"
            ],
            $id_manager->getAllPCIds()
        );
    }
}
