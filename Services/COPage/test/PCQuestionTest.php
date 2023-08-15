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
class PCQuestionTest extends COPageTestBase
{
    public function testConstruction(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCQuestion($page);
        $this->assertEquals(
            ilPCQuestion::class,
            get_class($pc)
        );
    }

    public function testCreate(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCQuestion($page);
        $pc->create($page, "pg", "");
        $this->assertXmlEquals(
            '<PageObject HierId="pg"><PageContent><Question QRef=""></Question></PageContent></PageObject>',
            $page->getXMLFromDom()
        );
    }

    public function testQuestionReference(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCQuestion($page);
        $pc->create($page, "pg", "");
        $pc->setQuestionReference("qref1");

        $this->assertEquals(
            "qref1",
            $pc->getQuestionReference()
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><Question QRef="qref1"></Question></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }
}
