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
        $this->pc_cnt = 1;
    }

    /**
     * @return ContentIdGenerator|(ContentIdGenerator&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getIdGeneratorMock()
    {
        $gen = $this->createMock(ContentIdGenerator::class);
        $gen->method("generate")
            ->willReturnCallback(function () {
                return str_pad(
                    (string) $this->pc_cnt++,
                    32,
                    "0",
                    STR_PAD_LEFT
                );
            });
        return $gen;
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

        $id_manager = new ContentIdManager(
            $page,
            $this->getIdGeneratorMock()
        );

        $id_manager->insertPCIds();

        $expected = <<<EOT
<PageObject HierId="pg"><PageContent PCID="00000000000000000000000000000001"><Grid><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="6" WIDTH_L="6" WIDTH_XL="6" PCID="00000000000000000000000000000002"/><GridCell WIDTH_XS="" WIDTH_S="12" WIDTH_M="6" WIDTH_L="6" WIDTH_XL="6" PCID="00000000000000000000000000000003"/></Grid></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }
}
