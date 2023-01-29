<?php

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
