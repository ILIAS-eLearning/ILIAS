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

namespace ILIAS\COPage\Test\PC;

use PHPUnit\Framework\TestCase;
use ILIAS\COPage\PC\PCFactory;
use ILIAS\COPage\Page\PageContentManager;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PCFactoryTest extends \COPageTestBase
{
    public function testGetByNode(): void
    {
        global $DIC;

        $dom_util = new \ILIAS\COPage\Dom\DomUtil();
        $page = $this->getEmptyPageWithDom();
        $page_content = new PageContentManager($page->getDomDoc());
        $pc_factory = new PCFactory($def = $this->getPCDefinition());

        $this->insertParagraphAt($page, "pg", "Hello");
        $this->insertParagraphAt($page, "1", "World");
        $page->insertPCIds();

        $node = $page_content->getContentDomNode("1");
        $pc = $pc_factory->getByNode($node, $page);

        $this->assertEquals(
            \ilPCParagraph::class,
            get_class($pc)
        );

        $this->assertEquals(
            "Hello",
            $pc->getText()
        );
    }
}
