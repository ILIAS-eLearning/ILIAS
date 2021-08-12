<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestParticipantScoringTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestParticipantScoringTest extends ilTestBaseTestCase
{
    private ilTestParticipantScoring $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestParticipantScoring();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestParticipantScoring::class, $this->testObj);
    }
}