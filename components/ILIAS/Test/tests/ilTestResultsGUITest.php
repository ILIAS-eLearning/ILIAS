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
 * Class ilTestResultsGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestResultsGUITest extends ilTestBaseTestCase
{
    private ilTestResultsGUI $testObj;

    protected function setUp(): void
    {
        global $DIC;
        parent::setUp();

        $this->addGlobal_ilTabs();
        $this->addGlobal_ilToolbar();

        $this->testObj = new ilTestResultsGUI(
            $this->getTestObjMock(),
            $DIC['ilCtrl'],
            $this->createMock(ilTestAccess::class),
            $DIC['ilDB'],
            $DIC['refinery'],
            $DIC['ilUser'],
            $DIC['lng'],
            $this->createMock(\ILIAS\Test\Logging\TestLogger::class),
            $DIC['component.repository'],
            $this->createMock(ILIAS\Test\Presentation\TabsManager::class),
            $DIC['ilToolbar'],
            $DIC['tpl'],
            $DIC['ui.factory'],
            $DIC['ui.renderer'],
            $this->createMock(ILIAS\Skill\Service\SkillService::class),
            $this->createMock(ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository::class),
            $this->createMock(ILIAS\Test\RequestDataCollector::class),
            $DIC['http'],
            $this->createMock(ILIAS\Data\Factory::class),
            $this->createMock(ilTestSession::class),
            $this->createMock(ilTestObjectiveOrientedContainer::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestResultsGUI::class, $this->testObj);
    }
}
