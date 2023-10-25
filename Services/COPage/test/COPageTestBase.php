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
class COPageTestBase extends TestCase
{
    protected int $pc_cnt;

    /**
     * @param mixed $value
     */
    protected function setGlobalVariable(string $name, $value): void
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = static function (\ILIAS\DI\Container $c) use ($value) {
            return $value;
        };
    }

    protected function setUp(): void
    {
        $dic = new ILIAS\DI\Container();
        $GLOBALS['DIC'] = $dic;

        if (!defined("ILIAS_LOG_ENABLED")) {
            define("ILIAS_LOG_ENABLED", false);
        }

        if (!defined("IL_INST_ID")) {
            define("IL_INST_ID", 0);
        }

        if (!defined("COPAGE_TEST")) {
            define("COPAGE_TEST", "1");
        }
        parent::setUp();

        $def_mock = $this->getMockBuilder(ilObjectDefinition::class)
                         ->disableOriginalConstructor()
                         ->getMock();

        $def_mock
            ->method('getAllRepositoryTypes')
            ->willReturn(["crs", "grp", "cat"]);
        $this->setGlobalVariable(
            "objDefinition",
            $def_mock
        );

        $db_mock = $this->createMock(ilDBInterface::class);
        $this->setGlobalVariable(
            "ilDB",
            $db_mock
        );

        $this->setGlobalVariable(
            "ilAccess",
            $this->createConfiguredMock(
                ilAccess::class,
                [
                    "checkAccess" => true
                ]
            )
        );

        $ctrl = $this->getMockBuilder('ilCtrl')->disableOriginalConstructor()->onlyMethods(
            ['setParameterByClass', 'redirectByClass', 'forwardCommand']
        )->getMock();
        $ctrl->method('setParameterByClass');
        $ctrl->method('redirectByClass');
        $this->setGlobalVariable('ilCtrl', $ctrl);

        $languageMock = $this->getMockBuilder(ilLanguage::class)
                             ->disableOriginalConstructor()
                             ->getMock();
        $this->setGlobalVariable(
            "lng",
            $languageMock
        );

        $userMock = $this->getMockBuilder(ilObjUser::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $this->setGlobalVariable(
            "ilUser",
            $userMock
        );

        $treeMock = $this->getMockBuilder(ilTree::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $this->setGlobalVariable(
            "tree",
            $treeMock
        );

        $refinery_mock = $this->createMock(ILIAS\Refinery\Factory::class);
        $this->setGlobalVariable(
            "refinery",
            $refinery_mock
        );

        $this->pc_cnt = 1;
    }

    /**
     * @return ContentIdGenerator|(ContentIdGenerator&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getIdGeneratorMock()
    {
        $gen = $this->createMock(\ILIAS\COPage\ID\ContentIdGenerator::class);
        $gen->method("generate")
            ->willReturnCallback(function () {
                return str_pad(
                    (string) $this->pc_cnt++,
                    32,
                    "0",
                    STR_PAD_LEFT
                );
            });
        return $gen;
    }

    protected function getPCDefinition(): ilUnitTestPCDefinition
    {
        return new ilUnitTestPCDefinition();
    }

    protected function setPCIdCnt(int $cnt): void
    {
        $this->pc_cnt = $cnt;
    }

    protected function getIDManager(\ilPageObject $page): \ILIAS\COPage\ID\ContentIdManager
    {
        return new \ILIAS\COPage\ID\ContentIdManager(
            $page,
            $this->getIdGeneratorMock()
        );
    }

    protected function insertParagraphAt(
        \ilPageObject $page,
        string $hier_id,
        string $text = ""
    ) {
        $pc = new \ilPCParagraph($page);
        $pc->create($page, $hier_id);
        $pc->setLanguage("en");
        if ($text !== "") {
            $pc->setText($text);
        }
        $page->addHierIDs();
    }

    protected function tearDown(): void
    {
    }

    protected function normalize(string $html): string
    {
        return trim(str_replace(["\n", "\r"], "", $html));
    }

    protected function assertXmlEquals(string $expected_xml_as_string, string $html_xml_string): void
    {
        $html = new DOMDocument();
        $html->formatOutput = true;
        $html->preserveWhiteSpace = false;
        $expected = new DOMDocument();
        $expected->formatOutput = true;
        $expected->preserveWhiteSpace = false;
        $html->loadXML($this->normalize($html_xml_string));
        $expected->loadXML($this->normalize($expected_xml_as_string));
        $this->assertEquals($expected->saveHTML(), $html->saveHTML());
    }


    protected function getEmptyPageWithDom(): ilUnitTestPageObject
    {
        $page = new ilUnitTestPageObject(0);
        $page->setContentIdManager($this->getIDManager($page));
        $page->setXMLContent("<PageObject></PageObject>");
        $page->buildDom();
        $page->addHierIDs();
        return $page;
    }

    // see saveJs in ilPCParagraph
    protected function legacyHtmlToXml(string $content): string
    {
        $content = str_replace("<br>", "<br />", $content);
        $content = ilPCParagraph::handleAjaxContent($content);
        $content = ilPCParagraph::_input2xml($content["text"], true, false);
        $content = ilPCParagraph::handleAjaxContentPost($content);
        return $content;
    }

    /**
     * @return (ilObjMediaObject&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getMediaObjectMock()
    {
        $media_item = new ilMediaItem();
        $media_item->setWidth("100");
        $media_item->setHeight("50");
        $media_object = $this->getMockBuilder(ilObjMediaObject::class)
                             ->disableOriginalConstructor()
                             ->getMock();
        $media_object->method("getMediaItem")
                     ->willReturnCallback(fn() => $media_item);
        return $media_object;
    }
}
