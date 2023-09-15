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
class PCProfileTest extends COPageTestBase
{
    public function testConstruction(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCProfile($page);
        $this->assertEquals(
            ilPCProfile::class,
            get_class($pc)
        );
    }

    public function testCreate(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCProfile($page);
        $pc->create($page, "pg", "", "MyPlugin", "1.0");
        $this->assertXmlEquals(
            '<PageObject HierId="pg"><PageContent><Profile></Profile></PageContent></PageObject>',
            $page->getXMLFromDom()
        );
    }

    public function testFields(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCProfile($page);
        $pc->create($page, "pg", "");
        $pc->setFields(
            "manual",
            [
                "Firstname","Lastname"
            ]
        );

        $this->assertEquals(
            [
            0 => "Firstname",
            1 => "Lastname"
        ],
            $pc->getFields()
        );

        $this->assertEquals(
            "manual",
            $pc->getMode()
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><Profile Mode="manual" User="0"><ProfileField Name="Firstname"></ProfileField><ProfileField Name="Lastname"></ProfileField></Profile></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testFieldsAll(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCProfile($page);
        $pc->create($page, "pg", "");
        $pc->setFields(
            "all"
        );

        $this->assertEquals(
            [
        ],
            $pc->getFields()
        );

        $this->assertEquals(
            "all",
            $pc->getMode()
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><Profile Mode="all" User="0"></Profile></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }
}
