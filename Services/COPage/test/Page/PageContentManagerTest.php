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

        $page_content->deleteContent("1");

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

        $page_content->deleteContents(["1", "2"]);

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
}
