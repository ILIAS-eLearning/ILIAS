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

use ILIAS\Test\Scoring\Manual\TestScoringByQuestionTableGUI;
use ILIAS\Test\Scoring\Manual\TestScoringByQuestionGUI;

/**
 * @author Marvin Beym <mbeym@databay.de>
 */
class TestScoringByQuestionTableGUITest extends ilTestBaseTestCase
{
    private TestScoringByQuestionTableGUI $tableGui;
    private TestScoringByQuestionGUI $parentObj_mock;

    protected function setUp(): void
    {
        global $DIC;
        parent::setUp();

        $lng_mock = $this->createMock(ilLanguage::class);
        $lng_mock->expects($this->any())
            ->method('txt')
            ->willReturnCallback(function () {
                return 'testTranslation';
            });
        $this->setGlobalVariable('lng', $lng_mock);

        $ctrl_mock = $this->createMock(ilCtrl::class);
        $ctrl_mock->expects($this->any())
            ->method('getFormAction')
            ->willReturnCallback(function () {
                return 'testFormAction';
            });
        $this->setGlobalVariable('ilCtrl', $ctrl_mock);

        $component_factory = $this->createMock(ilComponentFactory::class);
        $component_factory->method('getActivePluginsInSlot')->willReturn(new ArrayIterator());
        $this->setGlobalVariable('component.factory', $component_factory);

        $objTest_mock = $this->getTestObjMock();
        $objTest_mock->expects($this->any())
            ->method('getTestQuestions')
            ->willReturnCallback(function () {
                return [];
            });
        $objTest_mock->expects($this->any())
            ->method('getPotentialRandomTestQuestions')
            ->willReturnCallback(function () {
                return [];
            });

        $this->parentObj_mock = $this->getMockBuilder(TestScoringByQuestionGUI::class)
            ->disableOriginalConstructor()->onlyMethods(['getObject'])->getMock();
        $this->parentObj_mock->expects($this->any())->method('getObject')->willReturn($objTest_mock);

        $this->tableGui = new TestScoringByQuestionTableGUI(
            $this->parentObj_mock,
            $this->createMock(ilAccess::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(TestScoringByQuestionTableGUI::class, $this->tableGui);
    }
}
