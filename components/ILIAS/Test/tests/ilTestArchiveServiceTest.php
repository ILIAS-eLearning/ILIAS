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
 * Class ilTestArchiveServiceTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestArchiveServiceTest extends ilTestBaseTestCase
{
    private ilTestArchiveService $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestArchiveService(
            $this->getTestObjMock(),
            $this->createMock(ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository::class),
            $this->createMock(ILIAS\Test\RequestDataCollector::class),
            $this->createMock(ilLanguage::class),
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilCtrl::class),
            $this->createMock(ilObjUser::class),
            $this->createMock(ilTabsGUI::class),
            $this->createMock(ilToolbarGUI::class),
            $this->createMock(ilGlobalTemplateInterface::class),
            $this->createMock(ILIAS\UI\Factory::class),
            $this->createMock(ILIAS\UI\Renderer::class),
            $this->createMock(ILIAS\HTTP\GlobalHttpState::class),
            $this->createMock(ILIAS\Refinery\Factory::class),
            $this->createMock(ilAccess::class),
            $this->createMock(ILIAS\ResourceStorage\Services::class),
            $this->createMock(ilObjectDataCache::class),
            $this->createMock(ilTestHTMLGenerator::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestArchiveService::class, $this->testObj);
    }

    public function testParticipantData(): void
    {
        $testParticipantData_mock = $this->createMock(ilTestParticipantData::class);

        $this->testObj->setParticipantData($testParticipantData_mock);

        $this->assertEquals($testParticipantData_mock, $this->testObj->getParticipantData());
    }
}
