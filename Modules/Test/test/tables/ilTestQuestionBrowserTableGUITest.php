<?php

declare(strict_types=1);

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

/**
 * Class ilTestQuestionBrowserTableGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestQuestionBrowserTableGUITest extends ilTestBaseTestCase
{
    private ilTestQuestionBrowserTableGUI $tableGui;
    private ilObjTestGUI $parentObj_mock;

    protected function setUp(): void
    {
        parent::setUp();

        $lng_mock = $this->createMock(ilLanguage::class);
        $lng_mock
                 ->method("txt")
                 ->willReturnCallback(function () {
                     return "testTranslation";
                 });

        $ctrl_mock = $this->createMock(ilCtrl::class);
        $ctrl_mock
                  ->method("getFormAction")
                  ->willReturnCallback(function () {
                      return "testFormAction";
                  });

        $mainTpl_mock = $this->createMock(ilGlobalPageTemplate::class);
        $db_mock = $this->createMock(ilDBInterface::class);
        $tree_mock = $this->createMock(ilTree::class);
        $this->setGlobalVariable("lng", $lng_mock);
        $this->setGlobalVariable("ilCtrl", $ctrl_mock);
        $this->setGlobalVariable("tpl", $mainTpl_mock);
        $this->setGlobalVariable("tree", $tree_mock);
        $this->setGlobalVariable("ilDB", $db_mock);
        $this->setGlobalVariable("ilUser", $this->createMock(ilObjUser::class));
        $this->setGlobalVariable("ilObjDataCache", $this->createMock(ilObjectDataCache::class));
        $component_factory = $this->createMock(ilComponentFactory::class);
        $component_factory->method("getActivePluginsInSlot")->willReturn(new ArrayIterator());
        $this->setGlobalVariable("component.factory", $component_factory);

        $component_repository = $this->createMock(ilComponentRepository::class);
        $this->setGlobalVariable("component.repository", $component_repository);

        $this->parentObj_mock = $this->getMockBuilder(ilObjTestGUI::class)->disableOriginalConstructor()->onlyMethods(array('getObject'))->getMock();
        $this->parentObj_mock->method('getObject')->willReturn($this->createMock(ilObjTest::class));
        $this->tableGui = new ilTestQuestionBrowserTableGUI(
            $ctrl_mock,
            $mainTpl_mock,
            $this->getMockBuilder(ilTabsGUI::class)->disableOriginalConstructor()->getMock(),
            $lng_mock,
            $tree_mock,
            $db_mock,
            $component_repository,
            $this->getMockBuilder(ilObjTest::class)->disableOriginalConstructor()->getMock(),
            $this->createMock(ilAccessHandler::class),
            $this->createMock(\ILIAS\HTTP\GlobalHttpState::class),
            new \ILIAS\Refinery\Factory(
                new \ILIAS\Data\Factory(),
                $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock()
            ),
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestQuestionBrowserTableGUI::class, $this->tableGui);
    }

    public function testWriteAccess(): void
    {
        $this->tableGui->setWriteAccess(false);
        $this->assertFalse($this->tableGui->hasWriteAccess());
        $this->tableGui->setWriteAccess(true);
        $this->assertTrue($this->tableGui->hasWriteAccess());
    }
}
