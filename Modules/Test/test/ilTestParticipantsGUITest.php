<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestParticipantsGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestParticipantsGUITest extends ilTestBaseTestCase
{
    private ilTestParticipantsGUI $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestParticipantsGUI(
            $this->createMock(ilObjTest::class),
            $this->createMock(ilTestQuestionSetConfig::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestParticipantsGUI::class, $this->testObj);
    }
}