<?php

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

declare(strict_types=1);

/**
 * Class ilTestQuestionNavigationGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestQuestionNavigationGUITest extends ilTestBaseTestCase
{
    use UITestHelper;

    private ilTestQuestionNavigationGUI $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $ui_factory = $test_helper->factory();
        $ui_renderer = $test_helper->renderer();

        $this->testObj = new ilTestQuestionNavigationGUI(
            $this->createMock(ilLanguage::class),
            $ui_factory,
            $ui_renderer
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestQuestionNavigationGUI::class, $this->testObj);
    }

    public function testEditSolutionCommand(): void
    {
        $editSolutionCommand = 'test';
        $this->testObj->setEditSolutionCommand($editSolutionCommand);
        $this->assertEquals($editSolutionCommand, $this->testObj->getEditSolutionCommand());
    }

    public function testQuestionWorkedThrough(): void
    {
        $this->testObj->setQuestionWorkedThrough(false);
        $this->assertFalse($this->testObj->isQuestionWorkedThrough());

        $this->testObj->setQuestionWorkedThrough(true);
        $this->assertTrue($this->testObj->isQuestionWorkedThrough());
    }

    public function testSubmitSolutionCommand(): void
    {
        $submitSolutionCommand = 'test';
        $this->testObj->setSubmitSolutionCommand($submitSolutionCommand);
        $this->assertEquals($submitSolutionCommand, $this->testObj->getSubmitSolutionCommand());
    }

    public function testRevertChangesLinkTarget(): void
    {
        $revertChangesLinkTarget = 'test';
        $this->testObj->setRevertChangesLinkTarget($revertChangesLinkTarget);
        $this->assertEquals($revertChangesLinkTarget, $this->testObj->getRevertChangesLinkTarget());
    }

    public function testDiscardSolutionButtonEnabled(): void
    {
        $this->testObj->setDiscardSolutionButtonEnabled(false);
        $this->assertFalse($this->testObj->isDiscardSolutionButtonEnabled());

        $this->testObj->setDiscardSolutionButtonEnabled(true);
        $this->assertTrue($this->testObj->isDiscardSolutionButtonEnabled());
    }

    public function testSkipQuestionLinkTarget(): void
    {
        $skipQuestionLinkTarget = 'test';
        $this->testObj->setSkipQuestionLinkTarget($skipQuestionLinkTarget);
        $this->assertEquals($skipQuestionLinkTarget, $this->testObj->getSkipQuestionLinkTarget());
    }

    public function testInstantFeedbackCommand(): void
    {
        $instantFeedbackCommand = 'test';
        $this->testObj->setInstantFeedbackCommand($instantFeedbackCommand);
        $this->assertEquals($instantFeedbackCommand, $this->testObj->getInstantFeedbackCommand());
    }

    public function testAnswerFreezingEnabled(): void
    {
        $this->testObj->setAnswerFreezingEnabled(false);
        $this->assertFalse($this->testObj->isAnswerFreezingEnabled());

        $this->testObj->setAnswerFreezingEnabled(true);
        $this->assertTrue($this->testObj->isAnswerFreezingEnabled());
    }

    public function testForceInstantResponseEnabled(): void
    {
        $this->testObj->setForceInstantResponseEnabled(false);
        $this->assertFalse($this->testObj->isForceInstantResponseEnabled());

        $this->testObj->setForceInstantResponseEnabled(true);
        $this->assertTrue($this->testObj->isForceInstantResponseEnabled());
    }

    public function testRequestHintCommand(): void
    {
        $requestHintCommand = 'test';
        $this->testObj->setRequestHintCommand($requestHintCommand);
        $this->assertEquals($requestHintCommand, $this->testObj->getRequestHintCommand());
    }

    public function testShowHintsCommand(): void
    {
        $showHintsCommand = 'test';
        $this->testObj->setShowHintsCommand($showHintsCommand);
        $this->assertEquals($showHintsCommand, $this->testObj->getShowHintsCommand());
    }

    public function testHintRequestsExist(): void
    {
        $this->testObj->setHintRequestsExist(false);
        $this->assertFalse($this->testObj->hintRequestsExist());

        $this->testObj->setHintRequestsExist(true);
        $this->assertTrue($this->testObj->hintRequestsExist());
    }

    public function testQuestionMarkLinkTarget(): void
    {
        $questionMarkLinkTarget = 'test';
        $this->testObj->setQuestionMarkLinkTarget($questionMarkLinkTarget);
        $this->assertEquals($questionMarkLinkTarget, $this->testObj->getQuestionMarkLinkTarget());
    }

    public function testQuestionMarked(): void
    {
        $this->testObj->setQuestionMarked(false);
        $this->assertFalse($this->testObj->isQuestionMarked());

        $this->testObj->setQuestionMarked(true);
        $this->assertTrue($this->testObj->isQuestionMarked());
    }

    public function testAnythingRendered(): void
    {
        $this->assertFalse($this->testObj->isAnythingRendered());

        $this->testObj->setAnythingRendered();
        $this->assertTrue($this->testObj->isAnythingRendered());
    }
}
