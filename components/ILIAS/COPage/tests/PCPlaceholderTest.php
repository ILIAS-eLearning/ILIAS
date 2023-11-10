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
class PCPlaceholderTest extends COPageTestBase
{
    public function testConstruction(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCPlaceHolder($page);
        $this->assertEquals(
            ilPCPlaceHolder::class,
            get_class($pc)
        );
    }

    public function testCreate(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCPlaceHolder($page);
        $pc->create($page, "pg");
        $this->assertXmlEquals(
            '<PageObject HierId="pg"><PageContent><PlaceHolder></PlaceHolder></PageContent></PageObject>',
            $page->getXMLFromDom()
        );
    }

    public function testContentClass(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCPlaceHolder($page);
        $pc->create($page, "pg");
        $pc->setContentClass("MyClass");

        $this->assertEquals(
            "MyClass",
            $pc->getContentClass()
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><PlaceHolder ContentClass="MyClass"></PlaceHolder></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testHeight(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCPlaceHolder($page);
        $pc->create($page, "pg");
        $pc->setHeight("300");

        $this->assertEquals(
            "300",
            $pc->getHeight()
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><PlaceHolder Height="300"></PlaceHolder></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }
}
