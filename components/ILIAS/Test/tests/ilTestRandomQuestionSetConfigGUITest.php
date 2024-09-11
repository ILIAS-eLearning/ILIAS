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
 * Class ilTestRandomQuestionSetConfigGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetConfigGUITest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetConfigGUI $testObj;

    protected function setUp(): void
    {
        global $DIC;
        parent::setUp();

        $this->addGlobal_ilObjDataCache();

        $test_logger = $this->createMock(ILIAS\Test\Logging\TestLogger::class);
        $this->testObj = new ilTestRandomQuestionSetConfigGUI(
            $this->getTestObjMock(),
            $DIC['ilCtrl'],
            $DIC['ilUser'],
            $DIC['ilAccess'],
            $DIC['ui.factory'],
            $DIC['ui.renderer'],
            $this->createMock(\ILIAS\Test\Presentation\TabsManager::class),
            $DIC['lng'],
            $test_logger,
            $DIC['tpl'],
            $DIC['ilDB'],
            $DIC['tree'],
            $DIC['component.repository'],
            $DIC['objDefinition'],
            $DIC['ilObjDataCache'],
            $this->getMockBuilder(ilTestProcessLockerFactory::class)->disableOriginalConstructor()->getMock(),
            $this->createMock(ILIAS\Test\RequestDataCollector::class),
            $this->createMock(ILIAS\Test\Utilities\TitleColumnsBuilder::class),
            $this->createMock(\ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetConfigGUI::class, $this->testObj);
    }
}
