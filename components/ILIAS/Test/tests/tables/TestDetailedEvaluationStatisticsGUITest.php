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

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Test\RequestDataCollector;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use PHPUnit\Framework\MockObject\Exception as MockObjectException;

/**
 * Class ilTestDetailedEvaluationStatisticsTableGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class TestDetailedEvaluationStatisticsGUITest extends ilTestBaseTestCase
{
    private TestDetailedEvaluationStatisticsGUI $tableGui;
    private ilTestEvaluationGUI $parentObj_mock;

    /**
     * @throws MockObjectException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $lng_mock = $this->createMock(ilLanguage::class);
        $ctrl_mock = $this->createMock(ilCtrl::class);
        $ctrl_mock
              ->method('getFormAction')
              ->willReturn('testFormAction');

        $this->setGlobalVariable('lng', $lng_mock);
        $this->setGlobalVariable('ilCtrl', $ctrl_mock);
        $this->setGlobalVariable('tpl', $this->createMock(ilGlobalPageTemplate::class));
        $this->setGlobalVariable('component.repository', $this->createMock(ilComponentRepository::class));
        $component_factory = $this->createMock(ilComponentFactory::class);
        $component_factory->method('getActivePluginsInSlot')->willReturn(new ArrayIterator());
        $this->setGlobalVariable('component.factory', $component_factory);
        $this->setGlobalVariable('ilDB', $this->createMock(ilDBInterface::class));

        $this->parentObj_mock = $this->getMockBuilder(ilTestEvaluationGUI::class)->disableOriginalConstructor()->onlyMethods(['getObject'])->getMock();
        $this->parentObj_mock->method('getObject')->willReturn($this->getTestObjMock());
        $this->tableGui = new TestDetailedEvaluationStatisticsGUI(
            $this->createMock(ilGlobalTemplateInterface::class),
            $this->createMock(ilCtrl::class),
            $this->createMock(ilLanguage::class),
            $this->createMock(ilTabsGUI::class),
            $this->createMock(UIFactory::class),
            $this->createMock(UIRenderer::class),
            $this->createMock(ilObjTest::class),
            $this->createMock(RequestDataCollector::class),
            $this->createMock(ilTestAccess::class),
            $this->createMock(ilToolbarGUI::class),
            $this->createMock(GlobalHttpState::class),
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(TestDetailedEvaluationStatisticsGUI::class, $this->tableGui);
    }
}
