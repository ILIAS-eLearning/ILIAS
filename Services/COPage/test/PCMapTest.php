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
class PCMapTest extends COPageTestBase
{
    public function testConstruction(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCMap($page);
        $this->assertEquals(
            ilPCMap::class,
            get_class($pc)
        );
    }

    public function testCreate(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCMap($page);
        $pc->create($page, "pg");
        $this->assertXmlEquals(
            '<PageObject HierId="pg"><PageContent><Map Latitude="0" Longitude="0" Zoom="3"></Map></PageContent></PageObject>',
            $page->getXMLFromDom()
        );
    }

    public function testLatitude(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCMap($page);
        $pc->create($page, "pg");
        $pc->setLatitude(23.24);

        $this->assertEquals(
            23.24,
            $pc->getLatitude()
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><Map Latitude="23.24" Longitude="0" Zoom="3"></Map></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testLongitude(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCMap($page);
        $pc->create($page, "pg");
        $pc->setLongitude(44.24);

        $this->assertEquals(
            44.24,
            $pc->getLongitude()
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><Map Latitude="0" Longitude="44.24" Zoom="3"></Map></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testZoom(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCMap($page);
        $pc->create($page, "pg");
        $pc->setZoom(7);

        $this->assertEquals(
            7,
            $pc->getZoom()
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><Map Latitude="0" Longitude="0" Zoom="7"></Map></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testLayout(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCMap($page);
        $pc->create($page, "pg");
        $pc->setLayout("200", "100", "Right");

        $this->assertEquals(
            "200",
            $pc->getWidth()
        );

        $this->assertEquals(
            "100",
            $pc->getHeight()
        );

        $this->assertEquals(
            "Right",
            $pc->getHorizontalAlign()
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><Map Latitude="0" Longitude="0" Zoom="3"><Layout Width="200" Height="100" HorizontalAlign="Right"/></Map></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testCaption(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCMap($page);
        $pc->create($page, "pg");
        $pc->setCaption("Moin");

        $this->assertEquals(
            "Moin",
            $pc->getCaption()
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><Map Latitude="0" Longitude="0" Zoom="3"><MapCaption>Moin</MapCaption></Map></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }
}
