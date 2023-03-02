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
        $page->setXMLContent("<PageObject></PageObject>");
        $page->buildDom();
        $page->addHierIDs();
        return $page;
    }
}
