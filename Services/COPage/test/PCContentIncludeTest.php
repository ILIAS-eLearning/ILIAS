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
class PCContentIncludeTest extends COPageTestBase
{
    public function testConstruction(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCContentInclude($page);
        $this->assertEquals(
            ilPCContentInclude::class,
            get_class($pc)
        );
    }

    public function testCreate(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCContentInclude($page);
        $pc->create($page, "pg");
        $this->assertXmlEquals(
            '<PageObject HierId="pg"><PageContent><ContentInclude></ContentInclude></PageContent></PageObject>',
            $page->getXMLFromDom()
        );
    }

    public function testContentId(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCContentInclude($page);
        $pc->create($page, "pg");
        $pc->setContentId("10");

        $this->assertEquals(
            "10",
            $pc->getContentId()
        );

        $expected = <<<EOT
<PageObject HierId="pg"><PageContent><ContentInclude ContentId="10"></ContentInclude></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testContentType(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCContentInclude($page);
        $pc->create($page, "pg");
        $pc->setContentType("type");

        $this->assertEquals(
            "type",
            $pc->getContentType()
        );

        $expected = <<<EOT
<PageObject HierId="pg"><PageContent><ContentInclude ContentType="type"></ContentInclude></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testInstId(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCContentInclude($page);
        $pc->create($page, "pg");
        $pc->setInstId("123");

        $this->assertEquals(
            "123",
            $pc->getInstId()
        );

        $expected = <<<EOT
<PageObject HierId="pg"><PageContent><ContentInclude InstId="123"></ContentInclude></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testCollectContentIncludes(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCContentInclude($page);
        $pc->create($page, "pg");
        $pc->setInstId("123");
        $pc->setContentType("type");
        $pc->setContentId(55);

        $includes = ilPCContentInclude::collectContentIncludes($page, $page->getDomDoc());

        $this->assertEquals(
            ['type:55:123' => [
                "type" => "type",
                "id" => "55",
                "inst_id" => "123"
            ]],
            $includes
        );
    }
}
