<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestRandomQuestionSetConfigStateMessageHandlerTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetConfigStateMessageHandlerTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetConfigStateMessageHandler $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionSetConfigStateMessageHandler(
            $this->createMock(ilLanguage::class),
            $this->createMock(ilCtrl::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetConfigStateMessageHandler::class, $this->testObj);
    }
}