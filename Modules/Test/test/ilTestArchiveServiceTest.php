<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

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
