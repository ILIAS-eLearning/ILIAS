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
 * Class ilTestRandomQuestionSetConfigStateMessageHandlerTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetConfigStateMessageHandlerTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetConfigStateMessageHandler $testObj;

    protected function setUp(): void
    {
        parent::setUp();
        $this->addGlobal_ilUser();

        $this->testObj = new ilTestRandomQuestionSetConfigStateMessageHandler(
            $this->createMock(ilLanguage::class),
            $this->createMock(ILIAS\DI\UIServices::class),
            $this->createMock(ilCtrl::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetConfigStateMessageHandler::class, $this->testObj);
    }

    public function testLostPools(): void
    {
        $expected = [
            new ilTestRandomQuestionSetNonAvailablePool(),
            new ilTestRandomQuestionSetNonAvailablePool(),
            new ilTestRandomQuestionSetNonAvailablePool()
        ];

        $this->testObj->setLostPools($expected);
        $this->assertEquals($expected, $this->testObj->getLostPools());
    }

    public function testParticipantDataExists(): void
    {
        $this->testObj->setParticipantDataExists(false);
        $this->assertFalse($this->testObj->doesParticipantDataExists());

        $this->testObj->setParticipantDataExists(true);
        $this->assertTrue($this->testObj->doesParticipantDataExists());
    }

    public function testTargetGUI(): void
    {
        $targetGui_mock = $this->createMock(ilTestRandomQuestionSetConfigGUI::class);
        $this->testObj->setTargetGUI($targetGui_mock);
        $this->assertEquals($targetGui_mock, $this->testObj->getTargetGUI());
    }

    public function testContext(): void
    {
        $this->testObj->setContext("test");
        $this->assertEquals("test", $this->testObj->getContext());
    }

    public function testQuestionSetConfig(): void
    {
        $mock = $this->createMock(ilTestRandomQuestionSetConfig::class);
        $this->testObj->setQuestionSetConfig($mock);
        $this->assertEquals($mock, $this->testObj->getQuestionSetConfig());
    }
}
