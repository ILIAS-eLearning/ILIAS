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
            'questions',
            'passes',
            'passed',
            'lastVisit',
            'firstVisit',
            'timeOfWork',
            'numberOfQuestions',
            'questionsWorkedThrough',
            'mark_official',
            'mark',
            'maxpoints',
            'reached',
            'user_id',
            'login',
            'name',
            'passScoring'
        ];

        $this->assertEquals($expected, $this->testObj->__sleep());
    }

    public function testPassScoring(): void
    {
        $passScoring = 1;
        $this->testObj->setPassScoring($passScoring);
        $this->assertEquals($passScoring, $this->testObj->getPassScoring());
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
        $name = 'testName';
        $this->testObj->setName($name);
        $this->assertEquals($name, $this->testObj->getName());
    }

    public function testLogin(): void
    {
        $login = 'testLogin';
        $this->testObj->setLogin($login);
        $this->assertEquals($login, $this->testObj->getLogin());
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
        $reached = 220.55;
        $this->testObj->setReached($reached);
        $this->assertEquals($reached, $this->testObj->reached);
    }

    public function testGetReached(): void
    {
        $reachedPoints = 20;
        $testEvaluationPassData = new ilTestEvaluationPassData();
        $testEvaluationPassData->setReachedPoints($reachedPoints);

        $this->testObj->passes = [$testEvaluationPassData];

        $this->assertEquals($reachedPoints, $this->testObj->getReached());
    }

    public function testGetMaxpoints(): void
    {
        $maxpoints = 20;
        $testEvaluationPassData = new ilTestEvaluationPassData();
        $testEvaluationPassData->setMaxPoints($maxpoints);

        $this->testObj->passes = [$testEvaluationPassData];

        $this->assertEquals($maxpoints, $this->testObj->getMaxpoints());
    }

    public function testSetMaxpoints(): void
    {
        $max_points = 220.55;
        $this->testObj->setMaxpoints($max_points);
        $this->assertEquals($max_points, $this->testObj->maxpoints);
    }

    public function testGetReachedPointsInPercent(): void
    {
        $reachedpoints = 15;
        $maxpoints = 20;
        $testEvaluationPassData = new ilTestEvaluationPassData();
        $testEvaluationPassData->setReachedPoints($reachedpoints);
        $testEvaluationPassData->setMaxPoints($maxpoints);

        $this->testObj->passes = [$testEvaluationPassData];

        $this->assertEquals(($reachedpoints / $maxpoints) * 100, $this->testObj->getReachedPointsInPercent());
    }

    public function testMark(): void
    {
        $a_mark = 'testMark';
        $this->testObj->setMark($a_mark);
        $this->assertEquals($a_mark, $this->testObj->getMark());
    }

    public function testGetQuestionsWorkedThrough(): void
    {
        $reachedpoints = 15;
        $maxpoints = 20;
        $nrOfAnsweredQuestions = 5;
        $testEvaluationPassData = new ilTestEvaluationPassData();
        $testEvaluationPassData->setReachedPoints($reachedpoints);
        $testEvaluationPassData->setMaxPoints($maxpoints);
        $testEvaluationPassData->setNrOfAnsweredQuestions($nrOfAnsweredQuestions);

        $this->testObj->passes = [$testEvaluationPassData];

        $this->assertEquals($nrOfAnsweredQuestions, $this->testObj->getQuestionsWorkedThrough());
    }

    public function testSetQuestionsWorkedThrough(): void
    {
        $nr = 215;
        $this->testObj->setQuestionsWorkedThrough($nr);
        $this->assertEquals($nr, $this->testObj->questionsWorkedThrough);
    }

    public function testGetNumberOfQuestions(): void
    {
        $questioncount = 5;
        $testEvaluationPassData = new ilTestEvaluationPassData();
        $testEvaluationPassData->setQuestionCount($questioncount);

        $this->testObj->passes = [$testEvaluationPassData];

        $this->assertEquals($questioncount, $this->testObj->getNumberOfQuestions());
    }

    public function testSetNumberOfQuestions(): void
    {
        $nr = 215;
        $this->testObj->setNumberOfQuestions($nr);
        $this->assertEquals($nr, $this->testObj->numberOfQuestions);
    }

    public function testGetQuestionsWorkedThroughInPercent(): void
    {
        $questioncount = 5;
        $nrOfAnsweredQuestions = 3;
        $testEvaluationPassData = new ilTestEvaluationPassData();
        $testEvaluationPassData->setQuestionCount($questioncount);
        $testEvaluationPassData->setNrOfAnsweredQuestions($nrOfAnsweredQuestions);

        $this->testObj->passes = [$testEvaluationPassData];

        $this->assertEquals(($nrOfAnsweredQuestions / $questioncount) * 100, $this->testObj->getQuestionsWorkedThroughInPercent());
    }

    public function testGetTimeOfWork(): void
    {
        $workingtime1 = 5;
        $data1 = new ilTestEvaluationPassData();
        $data1->setWorkingTime($workingtime1);

        $workingtime2 = 7;
        $data2 = new ilTestEvaluationPassData();
        $data2->setWorkingTime($workingtime2);

        $this->testObj->passes = [
            $data1,
            $data2
        ];

        $this->assertEquals($workingtime1 + $workingtime2, $this->testObj->getTimeOfWork());
    }

    public function testSetTimeOfWork(): void
    {
        $time_of_work = '215';
        $this->testObj->setTimeOfWork($time_of_work);
        $this->assertEquals($time_of_work, $this->testObj->timeOfWork);
    }

    public function testFirstVisit(): void
    {
        $time = 2125;
        $this->testObj->setFirstVisit($time);

        $this->assertEquals($time, $this->testObj->getFirstVisit());
    }

    public function testLastVisit(): void
    {
        $time = 2125;
        $this->testObj->setLastVisit($time);

        $this->assertEquals($time, $this->testObj->getLastVisit());
    }

    public function testGetPasses(): void
    {
        $workingtime1 = 5;
        $data1 = new ilTestEvaluationPassData();
        $data1->setWorkingTime($workingtime1);

        $workingtime2 = 7;
        $data2 = new ilTestEvaluationPassData();
        $data2->setWorkingTime($workingtime2);

        $this->testObj->passes = [
            $data1,
            $data2
        ];

        $this->assertEquals([$data1, $data2], $this->testObj->getPasses());
    }

    public function testAddPasses(): void
    {
        $this->assertEquals(0, $this->testObj->getPassCount());

        $data = [
            0,
            0,
            1,
            1
        ];

        foreach ($data as $value) {
            $this->testObj->addPass($value, new ilTestEvaluationPassData());
        }

        $this->assertEquals(count(array_unique($data)), $this->testObj->getPassCount());
    }

    public function testGetPass(): void
    {
        $this->assertEquals(0, $this->testObj->getPassCount());

        $data = [
            0 => $expected = new ilTestEvaluationPassData(),
            1 => new ilTestEvaluationPassData(),
            2 => new ilTestEvaluationPassData()
        ];

        foreach ($data as $key => $value) {
            $this->testObj->addPass($key, $value);
        }

        $this->assertEquals($expected, $this->testObj->getPass(0));
    }

    public function testGetPassCount(): void
    {
        $this->assertEquals(0, $this->testObj->getPassCount());

        $data = [
            0,
            0,
            1,
            1
        ];

        foreach ($data as $value) {
            $this->testObj->addPass($value, new ilTestEvaluationPassData());
        }

        $this->assertEquals(count(array_unique($data)), $this->testObj->getPassCount());
    }

    public function testAddQuestionTitle(): void
    {
        $question_title = 'testString';
        $question_id = 0;
        $this->testObj->addQuestionTitle($question_id, $question_title);

        $this->assertEquals($question_title, $this->testObj->getQuestionTitles()[$question_id]);
    }

    public function testGetQuestions(): void
    {
        $this->assertNull($this->testObj->getQuestions());

        $expected = [
            'id' => $question_id = 22,
            'o_id' => $original_id = 20,
            'points' => $max_points = 15,
            'sequence' => $sequence = null
        ];

        $this->testObj->addQuestion($original_id, $question_id, $max_points, $sequence, 0);

        $this->assertEquals([$expected], $this->testObj->getQuestions());
    }

    public function testGetQuestion(): void
    {
        $expected = [
            'id' => $question_id = 22,
            'o_id' => $original_id = 20,
            'points' => $max_points = 15.0,
            'sequence' => $sequence = null
        ];

        $pass = 0;

        $this->testObj->addQuestion($original_id, $question_id, $max_points, $sequence, $pass);

        $this->assertEquals($expected, $this->testObj->getQuestion($pass));
    }

    public function testGetQuestionCount(): void
    {
        $questioncount = 5;
        $pass = new ilTestEvaluationPassData();
        $pass->setQuestionCount($questioncount);
        $this->testObj->addPass(0, $pass);

        $this->assertEquals($questioncount, $this->testObj->getQuestionCount());
    }

    public function testReachedPoints(): void
    {
        $reachedpoints = 25;
        $pass = new ilTestEvaluationPassData();
        $pass->setReachedPoints($reachedpoints);
        $this->testObj->addPass(0, $pass);

        $this->assertEquals($reachedpoints, $this->testObj->getReachedPoints());
    }

    public function testGetAvailablePoints(): void
    {
        $maxpoints = 25;
        $pass = new ilTestEvaluationPassData();
        $pass->setMaxPoints($maxpoints);
        $this->testObj->addPass(0, $pass);

        $this->assertEquals($maxpoints, $this->testObj->getAvailablePoints());
    }

    public function testGetReachedPointsInPercentForPass(): void
    {
        $reachedpoints = 25;
        $maxpoints = 50;
        $pass = new ilTestEvaluationPassData();
        $pass->setReachedPoints($reachedpoints);
        $pass->setMaxPoints($maxpoints);
        $pass_nr = 0;
        $this->testObj->addPass($pass_nr, $pass);

        $this->assertEquals($reachedpoints / $maxpoints, $this->testObj->getReachedPointsInPercentForPass($pass_nr));
    }

    public function testUserID(): void
    {
        $user_id = 120;
        $this->testObj->setUserID($user_id);
        $this->assertEquals($user_id, $this->testObj->getUserID());
    }

    public function testMarkOfficial(): void
    {
        $a_mark_official = 'test';
        $this->testObj->setMarkOfficial($a_mark_official);
        $this->assertEquals($a_mark_official, $this->testObj->getMarkOfficial());
    }
}
