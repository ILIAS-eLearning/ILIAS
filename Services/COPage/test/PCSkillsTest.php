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
class PCSkillsTest extends COPageTestBase
{
    public function testConstruction(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCSkills($page);
        $this->assertEquals(
            ilPCSkills::class,
            get_class($pc)
        );
    }

    public function testCreate(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCSkills($page);
        $pc->create($page, "pg", "");
        $this->assertXmlEquals(
            '<PageObject HierId="pg"><PageContent><Skills></Skills></PageContent></PageObject>',
            $page->getXMLFromDom()
        );
    }

    public function testData(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCSkills($page);
        $pc->create($page, "pg", "");
        $pc->setData("10");

        $this->assertEquals(
            "10",
            $pc->getSkillId()
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><Skills Id="10" User="0"></Skills></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }
}
