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
class PCLoginPageElementTest extends COPageTestBase
{
    public function testConstruction(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCLoginPageElement($page);
        $this->assertEquals(
            ilPCLoginPageElement::class,
            get_class($pc)
        );
    }

    public function testCreate(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCLoginPageElement($page);
        $pc->create($page, "pg");
        $this->assertXmlEquals(
            '<PageObject HierId="pg"><PageContent><LoginPageElement></LoginPageElement></PageContent></PageObject>',
            $page->getXMLFromDom()
        );
    }

    public function testType(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCLoginPageElement($page);
        $pc->create($page, "pg");
        $pc->setLoginPageElementType('user-agreement');

        $this->assertEquals(
            "user-agreement",
            $pc->getLoginPageElementType()
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><LoginPageElement Type="user-agreement"/></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testAlignment(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCLoginPageElement($page);
        $pc->create($page, "pg");
        $pc->setAlignment("Right");

        $this->assertEquals(
            "Right",
            $pc->getAlignment()
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><LoginPageElement HorizontalAlign="Right"/></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }
}
