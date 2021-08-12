<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestParticipantTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestParticipantTest extends ilTestBaseTestCase
{
    private ilTestParticipant $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestParticipant();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestParticipant::class, $this->testObj);
    }
}