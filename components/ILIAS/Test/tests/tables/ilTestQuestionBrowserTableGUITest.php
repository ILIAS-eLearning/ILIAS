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

/**
 * Class ilTestQuestionBrowserTableGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestQuestionBrowserTableGUITest extends ilTestBaseTestCase
{
    private ilTestQuestionBrowserTableGUI $tableGui;
    private ilObjTestGUI $parentObj_mock;

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
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

        $this->parentObj_mock = $this->getMockBuilder(ilObjTestGUI::class)->disableOriginalConstructor()->onlyMethods(['getObject'])->getMock();
        $this->parentObj_mock->method('getObject')->willReturn($this->getTestObjMock());
        $this->tableGui = new ilTestQuestionBrowserTableGUI(
            $this->getMockBuilder(ilTabsGUI::class)->disableOriginalConstructor()->getMock(),
            $tree_mock,
            $db_mock,
            $this->createMock(ILIAS\Test\Logging\TestLogger::class),
            $component_repository,
            $this->getMockBuilder(ilObjTest::class)->disableOriginalConstructor()->getMock(),
            $this->createMock(ilAccessHandler::class),
            $this->createMock(\ILIAS\HTTP\GlobalHttpState::class),
            new \ILIAS\Refinery\Factory(
                new \ILIAS\Data\Factory(),
                $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock()
            ),
            $this->createMock(ILIAS\UI\Factory::class),
            $this->createMock(ILIAS\UI\Renderer::class),
            $this->createMock(ILIAS\Test\RequestDataCollector::class),
            $this->createMock(ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository::class),
            $lng_mock,
            $ctrl_mock,
            $mainTpl_mock,
            $this->createMock(ilUIService::class),
            $this->createMock(ILIAS\Data\Factory::class),
            $this->createMock(ILIAS\Taxonomy\DomainService::class),
            fn(int $questionPoolId) => 'testLink'
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestQuestionBrowserTableGUI::class, $this->tableGui);
    }
}
