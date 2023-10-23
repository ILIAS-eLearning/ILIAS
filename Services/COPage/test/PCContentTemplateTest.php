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
class PCContentTemplateTest extends COPageTestBase
{
    public function testConstruction(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCContentTemplate($page);
        $this->assertEquals(
            ilPCContentTemplate::class,
            get_class($pc)
        );
    }

    /*
    public function testCreate(): void
    {
        $manager = new ilUnitTestPageManager();
        $page1 = $this->getEmptyPageWithDom();  // our template
        $template_xml = <<<EOT
<PageObject><PageContent><Table Language="en" DataTable="y">
<TableRow>
<TableData>
<PageContent><Paragraph Language="en" Characteristic="TableContent">one</Paragraph></PageContent>
</TableData>
<TableData>
<PageContent><Paragraph Language="en" Characteristic="TableContent">two</Paragraph></PageContent>
</TableData></TableRow></Table></PageContent></PageObject>
EOT;
        $page1->setXMLContent($template_xml);
        $page1->buildDom(true);

        $page2 = $this->getEmptyPageWithDom();  // our target page
        $manager->mockGet($page1);

        $temp = new ilPCContentTemplate($page2, $manager);

        $temp->create($page2, "pg", "", "x:1");
        $page2->stripPCIDs();
        $page2->stripHierIDs();

        $this->assertXmlEquals(
            $template_xml,
            $page2->getXMLFromDom()
        );
    }*/
}
