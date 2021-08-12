<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestParticipantsTimeExtensionGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestParticipantsTimeExtensionGUITest extends ilTestBaseTestCase
{
    private ilTestParticipantsTimeExtensionGUI $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestParticipantsTimeExtensionGUI($this->createMock(ilObjTest::class));
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestParticipantsTimeExtensionGUI::class, $this->testObj);
    }
}