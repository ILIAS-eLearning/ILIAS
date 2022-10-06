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
 * Class ilTestQuestionSideListGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestQuestionSideListGUITest extends ilTestBaseTestCase
{
    private ilTestQuestionSideListGUI $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestQuestionSideListGUI(
            $this->createMock(ilCtrl::class),
            $this->createMock(ilLanguage::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestQuestionSideListGUI::class, $this->testObj);
    }

    public function testTargetGUI(): void
    {
        $targetGui_mock = $this->createMock(ilTestPlayerAbstractGUI::class);
        $this->testObj->setTargetGUI($targetGui_mock);
        $this->assertEquals($targetGui_mock, $this->testObj->getTargetGUI());
    }

    public function testQuestionSummaryData(): void
    {
        $expected = [
            "test" => "Hello",
        ];
        $this->testObj->setQuestionSummaryData($expected);
        $this->assertEquals($expected, $this->testObj->getQuestionSummaryData());
    }

    public function testCurrentSequenceElement(): void
    {
        $this->testObj->setCurrentSequenceElement(125);
        $this->assertEquals(125, $this->testObj->getCurrentSequenceElement());
    }

    public function testCurrentPresentationMode(): void
    {
        $this->testObj->setCurrentPresentationMode("test");
        $this->assertEquals("test", $this->testObj->getCurrentPresentationMode());
    }

    public function testDisabled(): void
    {
        $this->testObj->setDisabled(false);
        $this->assertFalse($this->testObj->isDisabled());

        $this->testObj->setDisabled(true);
        $this->assertTrue($this->testObj->isDisabled());
    }
}
