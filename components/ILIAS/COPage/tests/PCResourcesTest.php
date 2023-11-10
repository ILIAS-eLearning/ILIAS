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
class PCResourcesTest extends COPageTestBase
{
    public function testConstruction(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCResources($page);
        $this->assertEquals(
            ilPCResources::class,
            get_class($pc)
        );
    }

    public function testCreate(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCResources($page);
        $pc->create($page, "pg", "");
        $this->assertXmlEquals(
            '<PageObject HierId="pg"><PageContent><Resources></Resources></PageContent></PageObject>',
            $page->getXMLFromDom()
        );
    }

    public function testResourceListType(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCResources($page);
        $pc->create($page, "pg", "");
        $pc->setResourceListType("glo");

        $this->assertEquals(
            "glo",
            $pc->getResourceListType()
        );
        $this->assertEquals(
            "ResourceList",
            $pc->getMainType()
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><Resources><ResourceList Type="glo"></ResourceList></Resources></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testItemGroupRefId(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCResources($page);
        $pc->create($page, "pg", "");
        $pc->setItemGroupRefId(22);

        $this->assertEquals(
            22,
            $pc->getItemGroupRefId()
        );
        $this->assertEquals(
            "ItemGroup",
            $pc->getMainType()
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><Resources><ItemGroup RefId="22"></ItemGroup></Resources></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }
}
