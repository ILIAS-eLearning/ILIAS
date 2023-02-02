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
class PageObjectTest extends COPageTestBase
{
    public function testConstruction(): void
    {
        $page = new ilUnitTestPageObject(0);
        $this->assertEquals(
            ilUnitTestPageObject::class,
            get_class($page)
        );
    }

    public function testSetXMLContent(): void
    {
        $page = new ilUnitTestPageObject(0);

        $page->setXMLContent("<PageObject></PageObject>");
        $this->assertEquals(
            "<PageObject></PageObject>",
            $page->getXMLContent()
        );
    }

    public function testGetXMLFromDom(): void
    {
        $page = new ilUnitTestPageObject(0);

        $page->setXMLContent("<PageObject></PageObject>");
        $page->buildDom();
        $this->assertXmlEquals(
            "<PageObject></PageObject>",
            $page->getXMLFromDom()
        );
    }

    public function testAddHierIds(): void
    {
        $page = new ilUnitTestPageObject(0);

        $page->setXMLContent("<PageObject></PageObject>");
        $page->buildDom();
        $page->addHierIDs();
        $this->assertXmlEquals(
            '<PageObject HierId="pg"></PageObject>',
            $page->getXMLFromDom()
        );
    }
}
