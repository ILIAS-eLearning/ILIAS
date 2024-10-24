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
 * Class ilTestParticipantsGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestParticipantsGUITest extends ilTestBaseTestCase
{
    private ilTestParticipantsGUI $testObj;

    protected function setUp(): void
    {
        global $DIC;
        parent::setUp();
        $this->addGlobal_ilAccess();
        $this->addGlobal_ilTabs();
        $this->addGlobal_ilToolbar();
        $this->addGlobal_ilComponentFactory();

        $this->testObj = new ilTestParticipantsGUI(
            $this->getTestObjMock(),
            $this->createMock(\ilObjUser::class),
            $this->createMock(ilTestObjectiveOrientedContainer::class),
            $this->createMock(ilTestQuestionSetConfig::class),
            $DIC['ilAccess'],
            $this->createMock(ilTestAccess::class),
            $DIC['tpl'],
            $DIC['ui.factory'],
            $DIC['ui.renderer'],
            $this->createMock(ilUIService::class),
            $this->createMock(ILIAS\Data\Factory::class),
            $DIC['lng'],
            $DIC['ilCtrl'],
            $DIC['refinery'],
            $DIC['ilDB'],
            $this->createMock(\ILIAS\Test\Presentation\TabsManager::class),
            $DIC['ilToolbar'],
            $DIC['component.factory'],
            $this->createMock(\ILIAS\Test\ExportImport\Factory::class),
            $this->createMock(\ILIAS\Test\RequestDataCollector::class),
            $this->createMock(\ILIAS\Test\ResponseHandler::class),
            $this->createMock(\ILIAS\Test\Participants\ParticipantRepository::class),
            $this->createMock(ILIAS\Test\Results\Data\Factory::class),
            $this->createMock(ILIAS\Test\Results\Presentation\Factory::class),
            $this->createMock(\ILIAS\Test\Results\Data\Repository::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestParticipantsGUI::class, $this->testObj);
    }
}
