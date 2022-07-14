<?php declare(strict_types=1);

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
 * Class ilObjTestTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilObjTestTest extends ilTestBaseTestCase
{
    private ilObjTest $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->addGlobal_ilUser();
        $this->addGlobal_lng();
        $this->addGlobal_ilias();
        $this->addGlobal_ilDB();
        $this->addGlobal_ilLog();
        $this->addGlobal_ilErr();
        $this->addGlobal_tree();
        $this->addGlobal_ilAppEventHandler();
        $this->addGlobal_objDefinition();

        $this->testObj = new ilObjTest();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilObjTest::class, $this->testObj);
    }

    public function testTmpCopyWizardCopyId() : void
    {
        $this->testObj->setTmpCopyWizardCopyId(12);
        $this->assertEquals(12, $this->testObj->getTmpCopyWizardCopyId());
    }

    public function testIntroductionEnabled() : void
    {
        $this->testObj->setIntroductionEnabled(false);
        $this->assertFalse($this->testObj->isIntroductionEnabled());

        $this->testObj->setIntroductionEnabled(true);
        $this->assertTrue($this->testObj->isIntroductionEnabled());
    }

    public function testIntroduction() : void
    {
        $this->assertEmpty($this->testObj->getIntroduction());
        $this->testObj->setIntroduction("Test");
        $this->assertEquals("Test", $this->testObj->getIntroduction());
    }

    public function testFinalStatement() : void
    {
        $this->assertEmpty($this->testObj->getFinalStatement());
        $this->testObj->setFinalStatement("Test");
        $this->assertEquals("Test", $this->testObj->getFinalStatement());
    }

    public function testShowInfo() : void
    {
        $this->testObj->setShowInfo(0);
        $this->assertEquals(0, $this->testObj->getShowInfo());

        $this->testObj->setShowInfo(1);
        $this->assertEquals(1, $this->testObj->getShowInfo());
    }

    public function testForceJS() : void
    {
        $this->testObj->setForceJS(0);
        $this->assertEquals(0, $this->testObj->getForceJS());

        $this->testObj->setForceJS(1);
        $this->assertEquals(1, $this->testObj->getForceJS());
    }

    public function testCustomStyle() : void
    {
        $this->testObj->setCustomStyle("Test");
        $this->assertEquals("Test", $this->testObj->getCustomStyle());
    }

    public function testShowFinalStatement() : void
    {
        $this->testObj->setShowFinalStatement(0);
        $this->assertEquals(0, $this->testObj->getShowFinalStatement());

        $this->testObj->setShowFinalStatement(1);
        $this->assertEquals(1, $this->testObj->getShowFinalStatement());
    }

    public function testTestId() : void
    {
        $this->testObj->setTestId(15);
        $this->assertEquals(15, $this->testObj->getTestId());
    }

    public function testECTSOutput() : void
    {
        $this->testObj->setECTSOutput(0);
        $this->assertEquals(0, $this->testObj->getECTSOutput());

        $this->testObj->setECTSOutput(1);
        $this->assertEquals(1, $this->testObj->getECTSOutput());
    }

    public function testECTSFX() : void
    {
        $this->testObj->setECTSFX(123);
        $this->assertEquals(123, $this->testObj->getECTSFX());
    }

    public function testECTSGrades() : void
    {
        $expected = [1, 6, 112, 160];
        $this->testObj->setECTSGrades($expected);
        $this->assertEquals($expected, $this->testObj->getECTSGrades());
    }

    public function testSequenceSettings() : void
    {
        $this->testObj->setSequenceSettings(0);
        $this->assertEquals(0, $this->testObj->getSequenceSettings());

        $this->testObj->setSequenceSettings(1);
        $this->assertEquals(1, $this->testObj->getSequenceSettings());
    }

    public function testIsPostponingEnabled() : void
    {
        $this->testObj->setSequenceSettings(0);
        $this->assertfalse($this->testObj->isPostponingEnabled());

        $this->testObj->setSequenceSettings(1);
        $this->assertTrue($this->testObj->isPostponingEnabled());
    }

    public function testSetPostponingEnabled() : void
    {
        $this->testObj->setPostponingEnabled(0);
        $this->assertfalse($this->testObj->isPostponingEnabled());

        $this->testObj->setPostponingEnabled(1);
        $this->assertTrue($this->testObj->isPostponingEnabled());
    }

    public function testScoreReporting() : void
    {
        $this->testObj->setScoreReporting(0);
        $this->assertEquals(0, $this->testObj->getScoreReporting());

        $this->testObj->setScoreReporting(1);
        $this->assertEquals(1, $this->testObj->getScoreReporting());
    }

    public function testInstantFeedbackSolution() : void
    {
        $this->testObj->setInstantFeedbackSolution(0);
        $this->assertEquals(0, $this->testObj->getInstantFeedbackSolution());

        $this->testObj->setInstantFeedbackSolution(200);
        $this->assertEquals(0, $this->testObj->getInstantFeedbackSolution());

        $this->testObj->setInstantFeedbackSolution(1);
        $this->assertEquals(1, $this->testObj->getInstantFeedbackSolution());
    }

    public function testGenericAnswerFeedback() : void
    {
        $this->testObj->setGenericAnswerFeedback(0);
        $this->assertEquals(0, $this->testObj->getGenericAnswerFeedback());

        $this->testObj->setGenericAnswerFeedback(200);
        $this->assertEquals(0, $this->testObj->getGenericAnswerFeedback());

        $this->testObj->setGenericAnswerFeedback(1);
        $this->assertEquals(1, $this->testObj->getGenericAnswerFeedback());
    }

    public function testAnswerFeedbackPoints() : void
    {
        $this->testObj->setAnswerFeedbackPoints(0);
        $this->assertEquals(0, $this->testObj->getAnswerFeedbackPoints());

        $this->testObj->setAnswerFeedbackPoints(200);
        $this->assertEquals(0, $this->testObj->getAnswerFeedbackPoints());

        $this->testObj->setAnswerFeedbackPoints(1);
        $this->assertEquals(1, $this->testObj->getAnswerFeedbackPoints());
    }

    public function testIsScoreReportingEnabled() : void
    {
        $this->testObj->setScoreReporting(ilObjTest::SCORE_REPORTING_FINISHED);
        $this->assertTrue($this->testObj->isScoreReportingEnabled());

        $this->testObj->setScoreReporting(ilObjTest::SCORE_REPORTING_IMMIDIATLY);
        $this->assertTrue($this->testObj->isScoreReportingEnabled());

        $this->testObj->setScoreReporting(ilObjTest::SCORE_REPORTING_DATE);
        $this->assertTrue($this->testObj->isScoreReportingEnabled());

        $this->testObj->setScoreReporting(ilObjTest::SCORE_REPORTING_AFTER_PASSED);
        $this->assertTrue($this->testObj->isScoreReportingEnabled());

        $this->testObj->setScoreReporting(ilObjTest::SCORE_REPORTING_DISABLED);
        $this->assertFalse($this->testObj->isScoreReportingEnabled());

        $this->testObj->setScoreReporting(999);
        $this->assertFalse($this->testObj->isScoreReportingEnabled());
    }

    public function testBlockPassesAfterPassedEnabled() : void
    {
        $this->testObj->setBlockPassesAfterPassedEnabled(false);
        $this->assertfalse($this->testObj->isBlockPassesAfterPassedEnabled());

        $this->testObj->setBlockPassesAfterPassedEnabled(true);
        $this->assertTrue($this->testObj->isBlockPassesAfterPassedEnabled());
    }

    public function testKiosk() : void
    {
        $this->testObj->setKiosk(0);
        $this->assertEquals(0, $this->testObj->getKiosk());

        $this->testObj->setKiosk(22);
        $this->assertEquals(22, $this->testObj->getKiosk());

        $this->testObj->setKiosk(1);
        $this->assertEquals(1, $this->testObj->getKiosk());
    }

    public function testGetKioskMode() : void
    {
        $this->testObj->setKiosk(0);
        $this->assertEquals(false, $this->testObj->getKioskMode());

        $this->testObj->setKiosk(22);
        $this->assertEquals(false, $this->testObj->getKioskMode());

        $this->testObj->setKiosk(1);
        $this->assertEquals(1, $this->testObj->getKioskMode());
    }

    public function testSetKioskMode() : void
    {
        $this->testObj->setKioskMode(false);
        $this->assertFalse($this->testObj->getKioskMode());

        $this->testObj->setKioskMode(true);
        $this->assertTrue($this->testObj->getKioskMode());
    }

    public function testStartingTimeEnabled() : void
    {
        $this->testObj->setStartingTimeEnabled(false);
        $this->assertFalse($this->testObj->isStartingTimeEnabled());

        $this->testObj->setStartingTimeEnabled(true);
        $this->assertTrue($this->testObj->isStartingTimeEnabled());
    }

    public function testStartingTime() : void
    {
        $this->testObj->setStartingTime("0");
        $this->assertEquals(0, $this->testObj->getStartingTime());

        $this->testObj->setStartingTime("1");
        $this->assertEquals(1, $this->testObj->getStartingTime());
    }

    public function testEndingTimeEnabled() : void
    {
        $this->testObj->setEndingTimeEnabled(false);
        $this->assertFalse($this->testObj->isEndingTimeEnabled());

        $this->testObj->setEndingTimeEnabled(true);
        $this->assertTrue($this->testObj->isEndingTimeEnabled());
    }

    public function testEndingTime() : void
    {
        $this->testObj->setEndingTime(0);
        $this->assertEquals(0, $this->testObj->getEndingTime());

        $this->testObj->setEndingTime(1);
        $this->assertEquals(1, $this->testObj->getEndingTime());
    }

    public function testNrOfTries() : void
    {
        $this->testObj->setNrOfTries(0);
        $this->assertEquals(0, $this->testObj->getNrOfTries());

        $this->testObj->setNrOfTries(22);
        $this->assertEquals(22, $this->testObj->getNrOfTries());

        $this->testObj->setNrOfTries(1);
        $this->assertEquals(1, $this->testObj->getNrOfTries());
    }

    public function testUsePreviousAnswers() : void
    {
        $this->testObj->setUsePreviousAnswers(0);
        $this->assertEquals(0, $this->testObj->getUsePreviousAnswers());

        $this->testObj->setUsePreviousAnswers(1);
        $this->assertEquals(1, $this->testObj->getUsePreviousAnswers());
    }

    public function testRedirectionMode() : void
    {
        $this->testObj->setRedirectionMode(0);
        $this->assertEquals(0, $this->testObj->getRedirectionMode());

        $this->testObj->setRedirectionMode(1);
        $this->assertEquals(1, $this->testObj->getRedirectionMode());
    }

    public function testRedirectionUrl() : void
    {
        $this->testObj->setRedirectionUrl("Test");
        $this->assertEquals("Test", $this->testObj->getRedirectionUrl());
    }

    public function testProcessingTime() : void
    {
        $this->testObj->setProcessingTime("Test");
        $this->assertEquals("Test", $this->testObj->getProcessingTime());
    }

    public function testSetProcessingTimeByMinutes() : void
    {
        $this->testObj->setProcessingTimeByMinutes(12);
        $this->assertEquals("00:12:00", $this->testObj->getProcessingTime());
    }

    public function testEnableProcessingTime() : void
    {
        $this->testObj->setEnableProcessingTime(0);
        $this->assertEquals(0, $this->testObj->getEnableProcessingTime());

        $this->testObj->setEnableProcessingTime(1);
        $this->assertEquals(1, $this->testObj->getEnableProcessingTime());
    }

    public function testResetProcessingTime() : void
    {
        $this->testObj->setResetProcessingTime(0);
        $this->assertEquals(0, $this->testObj->getResetProcessingTime());

        $this->testObj->setResetProcessingTime(1);
        $this->assertEquals(1, $this->testObj->getResetProcessingTime());
    }

    public function testPasswordEnabled() : void
    {
        $this->testObj->setPasswordEnabled(0);
        $this->assertEquals(0, $this->testObj->isPasswordEnabled());

        $this->testObj->setPasswordEnabled(1);
        $this->assertEquals(1, $this->testObj->isPasswordEnabled());
    }

    public function testPassword() : void
    {
        $this->testObj->setPassword("Test");
        $this->assertEquals("Test", $this->testObj->getPassword());
    }

    public function testPassWaiting() : void
    {
        $this->testObj->setPassWaiting("Test");
        $this->assertEquals("Test", $this->testObj->getPassWaiting());
    }

    public function testShuffleQuestions() : void
    {
        $this->testObj->setShuffleQuestions(0);
        $this->assertEquals(0, $this->testObj->getShuffleQuestions());

        $this->testObj->setShuffleQuestions(1);
        $this->assertEquals(1, $this->testObj->getShuffleQuestions());
    }

    public function testListOfQuestionsSettings() : void
    {
        $this->testObj->setListOfQuestionsSettings(0);
        $this->assertEquals(0, $this->testObj->getListOfQuestionsSettings());

        $this->testObj->setListOfQuestionsSettings(1);
        $this->assertEquals(1, $this->testObj->getListOfQuestionsSettings());

        $this->testObj->setListOfQuestionsSettings(22);
        $this->assertEquals(22, $this->testObj->getListOfQuestionsSettings());
    }

    public function testListOfQuestions() : void
    {
        $this->testObj->setListOfQuestions(0);
        $this->assertfalse($this->testObj->getListOfQuestions());

        $this->testObj->setListOfQuestions(1);
        $this->assertTrue($this->testObj->getListOfQuestions());
    }

    public function testResultsPresentation() : void
    {
        $this->testObj->setResultsPresentation(0);
        $this->assertEquals(0, $this->testObj->getResultsPresentation());

        $this->testObj->setResultsPresentation(1);
        $this->assertEquals(1, $this->testObj->getResultsPresentation());

        $this->testObj->setResultsPresentation(22);
        $this->assertEquals(22, $this->testObj->getResultsPresentation());
    }
}
