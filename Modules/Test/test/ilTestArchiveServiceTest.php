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
 * Class ilTestArchiveServiceTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestArchiveServiceTest extends ilTestBaseTestCase
{
    private ilTestArchiveService $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestArchiveService($this->createMock(ilObjTest::class));
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
