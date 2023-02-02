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
 * Class ilTestEvaluationUserDataTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestEvaluationUserDataTest extends ilTestBaseTestCase
{
    private ilTestEvaluationUserData $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestEvaluationUserData(0);
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestEvaluationUserData::class, $this->testObj);
    }

    public function test__sleep(): void
    {
        $expected = [
            "questions",
            "passes",
            "passed",
            "lastVisit",
            "firstVisit",
            "timeOfWork",
            "numberOfQuestions",
            "questionsWorkedThrough",
            "markECTS",
            "mark_official",
            "mark",
            "maxpoints",
            "reached",
            "user_id",
            "login",
            "name",
            "passScoring"
        ];

        $this->assertEquals($expected, $this->testObj->__sleep());
    }

    public function testPassScoring(): void
    {
        $expected = [1, 0, 20, 120, 12];

        $this->testObj->setPassScoring($expected);
        $this->assertEquals($expected, $this->testObj->getPassScoring());
    }

    public function testPassed(): void
    {
        $this->testObj->setPassed(true);
        $this->assertTrue($this->testObj->getPassed());

        $this->testObj->setPassed(false);
        $this->assertFalse($this->testObj->getPassed());
    }

    public function testName(): void
    {
        $this->testObj->setName("testName");
        $this->assertEquals("testName", $this->testObj->getName());
    }

    public function testLogin(): void
    {
        $this->testObj->setLogin("testLogin");
        $this->assertEquals("testLogin", $this->testObj->getLogin());
    }

    public function testSubmitted(): void
    {
        $this->testObj->setSubmitted(true);
        $this->assertTrue($this->testObj->isSubmitted());

        $this->testObj->setSubmitted(false);
        $this->assertFalse($this->testObj->isSubmitted());
    }

    public function testSetReached(): void
    {
        $this->testObj->setReached(220.55);
        $this->assertEquals(220.55, $this->testObj->reached);
    }

    public function testGetReached(): void
    {
        $testEvaluationPassData = new ilTestEvaluationPassData();
        $testEvaluationPassData->setReachedPoints(20);

        $this->testObj->passes = [
            $testEvaluationPassData
        ];

        $this->assertEquals(20, $this->testObj->getReached());
    }

    public function testGetMaxpoints(): void
    {
        $testEvaluationPassData = new ilTestEvaluationPassData();
        $testEvaluationPassData->setMaxPoints(20);

        $this->testObj->passes = [
            $testEvaluationPassData
        ];

        $this->assertEquals(20, $this->testObj->getMaxpoints());
    }

    public function testSetMaxpoints(): void
    {
        $this->testObj->setMaxpoints(220.55);
        $this->assertEquals(220.55, $this->testObj->maxpoints);
    }

    public function testGetReachedPointsInPercent(): void
    {
        $testEvaluationPassData = new ilTestEvaluationPassData();
        $testEvaluationPassData->setReachedPoints(15);
        $testEvaluationPassData->setMaxPoints(20);

        $this->testObj->passes = [
            $testEvaluationPassData
        ];

        $this->assertEquals(75, $this->testObj->getReachedPointsInPercent());
    }

    public function testMark(): void
    {
        $this->testObj->setMark("testMark");
        $this->assertEquals("testMark", $this->testObj->getMark());
    }

    public function testECTSMark(): void
    {
        $this->testObj->setECTSMark("testECTSMark");
        $this->assertEquals("testECTSMark", $this->testObj->getECTSMark());
    }

    public function testGetQuestionsWorkedThrough(): void
    {
        $testEvaluationPassData = new ilTestEvaluationPassData();
        $testEvaluationPassData->setReachedPoints(15);
        $testEvaluationPassData->setMaxPoints(20);
        $testEvaluationPassData->setNrOfAnsweredQuestions(5);

        $this->testObj->passes = [
            $testEvaluationPassData
        ];

        $this->assertEquals(5, $this->testObj->getQuestionsWorkedThrough());
    }

    public function testSetQuestionsWorkedThrough(): void
    {
        $this->testObj->setQuestionsWorkedThrough(215);
        $this->assertEquals(215, $this->testObj->questionsWorkedThrough);
    }

    public function testGetNumberOfQuestions(): void
    {
        $testEvaluationPassData = new ilTestEvaluationPassData();
        $testEvaluationPassData->setQuestionCount(5);

        $this->testObj->passes = [
            $testEvaluationPassData
        ];

        $this->assertEquals(5, $this->testObj->getNumberOfQuestions());
    }

    public function testSetNumberOfQuestions(): void
    {
        $this->testObj->setNumberOfQuestions(215);
        $this->assertEquals(215, $this->testObj->numberOfQuestions);
    }

    public function testGetQuestionsWorkedThroughInPercent(): void
    {
        $testEvaluationPassData = new ilTestEvaluationPassData();
        $testEvaluationPassData->setQuestionCount(5);
        $testEvaluationPassData->setNrOfAnsweredQuestions(3);

        $this->testObj->passes = [
            $testEvaluationPassData
        ];

        $this->assertEquals(60, $this->testObj->getQuestionsWorkedThroughInPercent());
    }

    public function testGetTimeOfWork(): void
    {
        $data1 = new ilTestEvaluationPassData();
        $data1->setWorkingTime(5);

        $data2 = new ilTestEvaluationPassData();
        $data2->setWorkingTime(7);

        $this->testObj->passes = [
            $data1,
            $data2
        ];

        $this->assertEquals(12, $this->testObj->getTimeOfWork());
    }

    public function testSetTimeOfWork(): void
    {
        $this->testObj->setTimeOfWork(215);
        $this->assertEquals(215, $this->testObj->timeOfWork);
    }

    public function testFirstVisit(): void
    {
        $this->testObj->setFirstVisit("2125");

        $this->assertEquals("2125", $this->testObj->getFirstVisit());
    }

    public function testLastVisit(): void
    {
        $this->testObj->setLastVisit("2125");

        $this->assertEquals("2125", $this->testObj->getLastVisit());
    }

    public function testGetPasses(): void
    {
        $data1 = new ilTestEvaluationPassData();
        $data1->setWorkingTime(5);

        $data2 = new ilTestEvaluationPassData();
        $data2->setWorkingTime(7);

        $this->testObj->passes = [
            $data1,
            $data2
        ];

        $this->assertEquals([$data1, $data2], $this->testObj->getPasses());
    }

    public function testAddPasses(): void
    {
        $this->assertEquals(0, $this->testObj->getPassCount());

        $this->testObj->addPass(0, new ilTestEvaluationPassData());
        $this->testObj->addPass(0, new ilTestEvaluationPassData());
        $this->testObj->addPass(1, new ilTestEvaluationPassData());

        $this->assertEquals(2, $this->testObj->getPassCount());
    }

    public function testGetPass(): void
    {
        $this->assertEquals(0, $this->testObj->getPassCount());

        $data = new ilTestEvaluationPassData();
        $this->testObj->addPass(3, $data);
        $this->testObj->addPass(0, new ilTestEvaluationPassData());
        $this->testObj->addPass(1, new ilTestEvaluationPassData());

        $this->assertEquals($data, $this->testObj->getPass(3));
    }

    public function testGetPassCount(): void
    {
        $this->assertEquals(0, $this->testObj->getPassCount());

        $this->testObj->addPass(0, new ilTestEvaluationPassData());
        $this->testObj->addPass(0, new ilTestEvaluationPassData());
        $this->testObj->addPass(1, new ilTestEvaluationPassData());

        $this->assertEquals(2, $this->testObj->getPassCount());
    }

    public function testAddQuestionTitle(): void
    {
        $this->testObj->addQuestionTitle(0, "testString");
        $this->testObj->addQuestionTitle(1, "testString2");

        $this->assertEquals("testString", $this->testObj->getQuestionTitles()[0]);
    }

    public function testGetQuestions(): void
    {
        $this->assertNull($this->testObj->getQuestions());

        $expected = [
            "id" => 22,
            "o_id" => 20,
            "points" => 15,
            "sequence" => null
        ];

        $this->testObj->addQuestion(20, 22, 15, null, 0);

        $this->assertEquals([$expected], $this->testObj->getQuestions());
    }

    public function testGetQuestion(): void
    {
        $expected = [
            "id" => 22,
            "o_id" => 20,
            "points" => 15,
            "sequence" => null
        ];

        $this->testObj->addQuestion(20, 22, 15, null, 0);

        $this->assertEquals($expected, $this->testObj->getQuestion(0));
    }

    public function testGetQuestionCount(): void
    {
        $pass = new ilTestEvaluationPassData();
        $pass->setQuestionCount(5);
        $this->testObj->addPass(0, $pass);

        $this->assertEquals(5, $this->testObj->getQuestionCount());
    }

    public function testReachedPoints(): void
    {
        $pass = new ilTestEvaluationPassData();
        $pass->setReachedPoints(25);
        $this->testObj->addPass(0, $pass);

        $this->assertEquals(25, $this->testObj->getReachedPoints());
    }

    public function testGetAvailablePoints(): void
    {
        $pass = new ilTestEvaluationPassData();
        $pass->setMaxPoints(25);
        $this->testObj->addPass(0, $pass);

        $this->assertEquals(25, $this->testObj->getAvailablePoints());
    }

    public function testGetReachedPointsInPercentForPass(): void
    {
        $pass = new ilTestEvaluationPassData();
        $pass->setReachedPoints(25);
        $pass->setMaxPoints(50);
        $this->testObj->addPass(0, $pass);

        $this->assertEquals(0.5, $this->testObj->getReachedPointsInPercentForPass(0));
    }

    public function testUserID(): void
    {
        $this->testObj->setUserID(120);
        $this->assertEquals(120, $this->testObj->getUserID());
    }

    public function testMarkOfficial(): void
    {
        $this->testObj->setMarkOfficial("test");
        $this->assertEquals("test", $this->testObj->getMarkOfficial());
    }
}
