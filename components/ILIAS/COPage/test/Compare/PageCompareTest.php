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

namespace ILIAS\COPage\Test\Compare;

use PHPUnit\Framework\TestCase;
use ILIAS\COPage\Compare\PageCompare;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PageCompareTest extends \COPageTestBase
{
    protected function getElementChange(string $pcid, array $changes): string
    {
        return $changes[str_pad($pcid, 32, "0", STR_PAD_LEFT)]["change"] ?? "";
    }
    public function testCompareEqual(): void
    {
        $compare = new PageCompare();

        $l_page = $this->getEmptyPageWithDom();
        $this->insertParagraphAt($l_page, "pg");
        $l_page->insertPCIds();

        $this->setPCIdCnt(1);   // reset PCID counter to get same PCID

        $r_page = $this->getEmptyPageWithDom();
        $this->insertParagraphAt($r_page, "pg");
        $r_page->insertPCIds();

        $res = $compare->compare($l_page, $l_page, $r_page);

        $this->assertEquals(
            "",
            $this->getElementChange("1", $res["l_changes"])
        );

        $this->assertEquals(
            "",
            $this->getElementChange("1", $res["r_changes"])
        );
    }

    public function testCompareNewDeleted(): void
    {
        $compare = new PageCompare();

        $l_page = $this->getEmptyPageWithDom();
        $this->insertParagraphAt($l_page, "pg");
        $l_page->insertPCIds();

        // Note, since we do not reset the PCID counter
        // the second will get a pc id of 2

        $r_page = $this->getEmptyPageWithDom();
        $this->insertParagraphAt($r_page, "pg");
        $r_page->insertPCIds();

        $res = $compare->compare($l_page, $l_page, $r_page);

        $this->assertEquals(
            "Deleted",
            $this->getElementChange("1", $res["l_changes"])
        );

        $this->assertEquals(
            "New",
            $this->getElementChange("2", $res["r_changes"])
        );
    }

    public function testCompareChanged(): void
    {
        $compare = new PageCompare();

        $l_page = $this->getEmptyPageWithDom();
        $this->insertParagraphAt($l_page, "pg", "Hello World!");
        $l_page->insertPCIds();

        $this->setPCIdCnt(1);   // reset PCID counter to get same PCID

        $r_page = $this->getEmptyPageWithDom();
        $this->insertParagraphAt($r_page, "pg", "Hello little World!");
        $r_page->insertPCIds();

        $res = $compare->compare($l_page, $l_page, $r_page);

        $this->assertEquals(
            "Modified",
            $this->getElementChange("1", $res["l_changes"])
        );

        $this->assertEquals(
            "Modified",
            $this->getElementChange("1", $res["r_changes"])
        );
    }

    public function testCompareTextChanges(): void
    {
        $compare = new PageCompare();

        $l_page = $this->getEmptyPageWithDom();
        $this->insertParagraphAt($l_page, "pg", "Hello World!");
        $l_page->insertPCIds();

        $this->setPCIdCnt(1);   // reset PCID counter to get same PCID

        $r_page = $this->getEmptyPageWithDom();
        $this->insertParagraphAt($r_page, "pg", "Hello little World!");
        $r_page->insertPCIds();

        $res = $compare->compare($l_page, $l_page, $r_page);

        $expected = <<<EOT
<PageObject HierId="pg"><PageContent HierId="1" PCID="00000000000000000000000000000001">Hello [ilDiffInsStart]little [ilDiffInsEnd]World!</PageContent><DivClass HierId="1" Class="ilEditModified"/></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $r_page->getXMLFromDom()
        );
    }
}
