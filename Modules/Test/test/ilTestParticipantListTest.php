<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestParticipantListTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestParticipantListTest extends ilTestBaseTestCase
{
    private ilTestParticipantList $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestParticipantList($this->createMock(ilObjTest::class));
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestParticipantList::class, $this->testObj);
    }
}