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

declare(strict_types=1);

namespace ILIAS\COPage\Test\Page;

use PHPUnit\Framework\TestCase;
use ILIAS\COPage\Page\PageContentManager;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PageContentManagerTest extends \COPageTestBase
{
    public function testGetContentDomNodePg(): void
    {
        $dom_util = new \ILIAS\COPage\Dom\DomUtil();
        $page = $this->getEmptyPageWithDom();
        $page_content = new PageContentManager($page->getDomDoc());

        $this->insertParagraphAt($page, "pg", "Hello");
        $this->insertParagraphAt($page, "1", "World");
        $page->insertPCIds();

        $node = $page_content->getContentDomNode("pg");

        $expected = <<<EOT
<PageObject HierId="pg"><PageContent HierId="1" PCID="00000000000000000000000000000001"><Paragraph Language="en">Hello</Paragraph></PageContent><PageContent HierId="2" PCID="00000000000000000000000000000002"><Paragraph Language="en">World</Paragraph></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $dom_util->dump($node)
        );
    }

    public function testGetContentDomNodePCId(): void
    {
        $dom_util = new \ILIAS\COPage\Dom\DomUtil();
        $page = $this->getEmptyPageWithDom();
        $page_content = new PageContentManager($page->getDomDoc());

        $this->insertParagraphAt($page, "pg", "Hello");
        $this->insertParagraphAt($page, "1", "World");
        $page->insertPCIds();

        $node = $page_content->getContentDomNode("", "00000000000000000000000000000002");

        $expected = <<<EOT
<PageContent HierId="2" PCID="00000000000000000000000000000002"><Paragraph Language="en">World</Paragraph></PageContent>
EOT;

        $this->assertXmlEquals(
            $expected,
            $dom_util->dump($node)
        );
    }

    public function testGetContentDomNodeHierId(): void
    {
        $dom_util = new \ILIAS\COPage\Dom\DomUtil();
        $page = $this->getEmptyPageWithDom();
        $page_content = new PageContentManager($page->getDomDoc());

        $this->insertParagraphAt($page, "pg", "Hello");
        $this->insertParagraphAt($page, "1", "World");
        $page->insertPCIds();

        $node = $page_content->getContentDomNode("1");

        $expected = <<<EOT
<PageContent HierId="1" PCID="00000000000000000000000000000001"><Paragraph Language="en">Hello</Paragraph></PageContent>
EOT;
        $this->assertXmlEquals(
            $expected,
            $dom_util->dump($node)
        );
    }

    public function testDeleteContent(): void
    {
        $dom_util = new \ILIAS\COPage\Dom\DomUtil();
        $page = $this->getEmptyPageWithDom();
        $page_content = new PageContentManager($page->getDomDoc());

        $this->insertParagraphAt($page, "pg", "Hello");
        $this->insertParagraphAt($page, "1", "World");
        $page->insertPCIds();

        $page_content->deleteContent($page, "1");

        $expected = <<<EOT
<PageObject HierId="pg"><PageContent HierId="2" PCID="00000000000000000000000000000002"><Paragraph Language="en">World</Paragraph></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testDeleteContents(): void
    {
        $dom_util = new \ILIAS\COPage\Dom\DomUtil();
        $page = $this->getEmptyPageWithDom();
        $page_content = new PageContentManager($page->getDomDoc());

        $this->insertParagraphAt($page, "pg", "Hello");
        $this->insertParagraphAt($page, "1", "little");
        $this->insertParagraphAt($page, "2", "World");
        $page->insertPCIds();

        $page_content->deleteContents($page, ["1", "2"]);

        $expected = <<<EOT
<PageObject HierId="pg"><PageContent HierId="3" PCID="00000000000000000000000000000003"><Paragraph Language="en">World</Paragraph></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testSwitchEnableMultiple(): void
    {
        $dom_util = new \ILIAS\COPage\Dom\DomUtil();
        $page = $this->getEmptyPageWithDom();
        $page_content = new PageContentManager(
            $page->getDomDoc(),
            $this->getPCDefinition()
        );

        $this->insertParagraphAt($page, "pg", "Hello");
        $this->insertParagraphAt($page, "1", "little");
        $this->insertParagraphAt($page, "2", "World");
        $page->insertPCIds();

        $page_content->switchEnableMultiple($page, ["1", "2"]);

        $expected = <<<EOT
<PageObject HierId="pg"><PageContent HierId="1" PCID="00000000000000000000000000000001" Enabled="False"><Paragraph Language="en">Hello</Paragraph></PageContent><PageContent HierId="2" PCID="00000000000000000000000000000002" Enabled="False"><Paragraph Language="en">little</Paragraph></PageContent><PageContent HierId="3" PCID="00000000000000000000000000000003"><Paragraph Language="en">World</Paragraph></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testInitialOpenedContent(): void
    {
        $dom_util = new \ILIAS\COPage\Dom\DomUtil();
        $page = $this->getEmptyPageWithDom();
        $page_content = new PageContentManager(
            $page->getDomDoc(),
            $this->getPCDefinition()
        );

        $this->insertParagraphAt($page, "pg", "Hello");
        $page->insertPCIds();

        $page_content->setInitialOpenedContent(
            "media",
            5,
            ""
        );

        $expected = [
            "id" => 5,
            "type" => "media",
            "target" => ""
        ];

        $this->assertEquals(
            $expected,
            $page_content->getInitialOpenedContent()
        );

        $expected = <<<EOT
<PageObject HierId="pg"><PageContent HierId="1" PCID="00000000000000000000000000000001"><Paragraph Language="en">Hello</Paragraph></PageContent><InitOpenedContent><IntLink Target="il__mob_5" Type="MediaObject" TargetFrame=""/></InitOpenedContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testInitialOpenedContent2(): void
    {
        $dom_util = new \ILIAS\COPage\Dom\DomUtil();
        $page = $this->getEmptyPageWithDom();
        $page_content = new PageContentManager(
            $page->getDomDoc(),
            $this->getPCDefinition()
        );

        $this->insertParagraphAt($page, "pg", "Hello");
        $page->insertPCIds();

        $page_content->setInitialOpenedContent(
            "media",
            5,
            ""
        );

        $page_content->setInitialOpenedContent(
            "term",
            10,
            "Glossary"
        );

        $expected = [
            "id" => 10,
            "type" => "term",
            "target" => "Glossary"
        ];

        $this->assertEquals(
            $expected,
            $page_content->getInitialOpenedContent()
        );

        $expected = <<<EOT
<PageObject HierId="pg"><PageContent HierId="1" PCID="00000000000000000000000000000001"><Paragraph Language="en">Hello</Paragraph></PageContent><InitOpenedContent><IntLink Target="il__git_10" Type="GlossaryItem" TargetFrame="Glossary"/></InitOpenedContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testInitialOpenedContent3(): void
    {
        $dom_util = new \ILIAS\COPage\Dom\DomUtil();
        $page = $this->getEmptyPageWithDom();
        $page_content = new PageContentManager(
            $page->getDomDoc(),
            $this->getPCDefinition()
        );

        $this->insertParagraphAt($page, "pg", "Hello");
        $page->insertPCIds();

        $page_content->setInitialOpenedContent(
            "media",
            5,
            ""
        );

        $page_content->setInitialOpenedContent();

        $expected = [];

        $this->assertEquals(
            $expected,
            $page_content->getInitialOpenedContent()
        );

        $expected = <<<EOT
<PageObject HierId="pg"><PageContent HierId="1" PCID="00000000000000000000000000000001"><Paragraph Language="en">Hello</Paragraph></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testInsertInstIntoIDsIntLink(): void
    {
        $dom_util = new \ILIAS\COPage\Dom\DomUtil();
        $page = $this->getEmptyPageWithDom();
        $page_content = new PageContentManager(
            $page->getDomDoc(),
            $this->getPCDefinition()
        );

        $xml = $this->legacyHtmlToXml(
            '<div id="1:1234" class="ilc_text_block_Standard">' .
            '[iln page="107"] xx [/iln]' .
            '</div>'
        );

        $this->insertParagraphAt($page, "pg", $xml);
        $page->insertPCIds();

        $page_content->insertInstIntoIDs("8877");

        $this->assertStringContainsString(
            '<IntLink Target="il_8877_pg_107" Type="PageObject">',
            $page->getXMLFromDom()
        );
    }

    public function testInsertInstIntoIDsFileItem(): void
    {
        $page = $this->getEmptyPageWithDom();
        $page_content = new PageContentManager(
            $page->getDomDoc(),
            $this->getPCDefinition()
        );

        $pc = new \ilPCFileList($page);
        $pc->create($page, "pg");
        $pc->appendItem(10, "file_loc", "image/jpeg");
        $page->insertPCIds();

        $page_content->insertInstIntoIDs("8877");

        $this->assertStringContainsString(
            '<Identifier Catalog="ILIAS" Entry="il_8877_file_10"/>',
            $page->getXMLFromDom()
        );
    }

    public function testInsertInstIntoIDsQuestion(): void
    {
        $page = $this->getEmptyPageWithDom();
        $page_content = new PageContentManager(
            $page->getDomDoc(),
            $this->getPCDefinition()
        );

        $pc = new \ilPCQuestion($page);
        $pc->create($page, "pg", "");
        $pc->setQuestionReference("il__qst_13");
        $page_content->insertInstIntoIDs("8877");

        $this->assertStringContainsString(
            '<Question QRef="il_8877_qst_13"/>',
            $page->getXMLFromDom()
        );
    }

    public function testInsertInstIntoIDsContentInclude(): void
    {
        $page = $this->getEmptyPageWithDom();
        $page_content = new PageContentManager(
            $page->getDomDoc(),
            $this->getPCDefinition()
        );

        $pc = new \ilPCContentInclude($page);
        $pc->create($page, "pg");
        $pc->setContentId(13);
        $page_content->insertInstIntoIDs("8877");

        $this->assertStringContainsString(
            '<ContentInclude ContentId="13" InstId="8877"/>',
            $page->getXMLFromDom()
        );
    }
}
