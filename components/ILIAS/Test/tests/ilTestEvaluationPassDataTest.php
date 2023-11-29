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
 * Class ilTestEvaluationPassDataTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestEvaluationPassDataTest extends ilTestBaseTestCase
{
    private ilTestEvaluationPassData $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestEvaluationPassData();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestEvaluationPassData::class, $this->testObj);
    }

    public function test__sleep(): void
    {
        $expected = [
            'answeredQuestions',
            'pass',
            'nrOfAnsweredQuestions',
            'reachedpoints',
            'maxpoints',
            'questioncount',
            'workingtime',
            'examId'
        ];
        $this->assertEquals($expected, $this->testObj->__sleep());
    }

    public function testNrOfAnsweredQuestions(): void
    {
        $nrOfAnsweredQuestions = 20;
        $this->testObj->setNrOfAnsweredQuestions($nrOfAnsweredQuestions);
        $this->assertEquals($nrOfAnsweredQuestions, $this->testObj->getNrOfAnsweredQuestions());
    }

    public function testReachedPoints(): void
    {
        $reachedpoints = 20;
        $this->testObj->setReachedPoints($reachedpoints);
        $this->assertEquals($reachedpoints, $this->testObj->getReachedPoints());
    }

    public function testMaxPoints(): void
    {
        $maxpoints = 20;
        $this->testObj->setMaxPoints($maxpoints);
        $this->assertEquals($maxpoints, $this->testObj->getMaxPoints());
    }

    public function testQuestionCount(): void
    {
        $questioncount = 20;
        $this->testObj->setQuestionCount($questioncount);
        $this->assertEquals($questioncount, $this->testObj->getQuestionCount());
    }

    public function testWorkingTime(): void
    {
        $workingtime = 20;
        $this->testObj->setWorkingTime($workingtime);
        $this->assertEquals($workingtime, $this->testObj->getWorkingTime());
    }

    public function testPass(): void
    {
        $pass = 20;
        $this->testObj->setPass($pass);
        $this->assertEquals($pass, $this->testObj->getPass());
    }

    public function testAnsweredQuestions(): void
    {
        $expected = [
            ['id' => 20, 'points' => 2.5, 'reached' => 1.5, 'isAnswered' => true, 'sequence' => null, 'manual' => 0],
            ['id' => 12, 'points' => 12.5, 'reached' => 11, 'isAnswered' => true, 'sequence' => null, 'manual' => 1],
            ['id' => 165, 'points' => -5.5, 'reached' => 0, 'isAnswered' => false, 'sequence' => null, 'manual' => 0],
            ['id' => 4, 'points' => 55.5, 'reached' => 200, 'isAnswered' => false, 'sequence' => null, 'manual' => 1]
        ];

        foreach ($expected as $value) {
            $this->testObj->addAnsweredQuestion(
                $value['id'],
                $value['points'],
                $value['reached'],
                $value['isAnswered'],
                $value['sequence'],
                $value['manual']
            );
        }

        $this->assertEquals($expected, $this->testObj->getAnsweredQuestions());

        foreach ($expected as $key => $value) {
            $this->assertEquals($value, $this->testObj->getAnsweredQuestion($key));
        }
    }

    public function testGetAnsweredQuestionByQuestionId(): void
    {
        $expected = [
            ['id' => 20, 'points' => 2.5, 'reached' => 1.5, 'isAnswered' => true, 'sequence' => null, 'manual' => 0],
            ['id' => 12, 'points' => 12.5, 'reached' => 11, 'isAnswered' => true, 'sequence' => null, 'manual' => 1],
            ['id' => 165, 'points' => -5.5, 'reached' => 0, 'isAnswered' => false, 'sequence' => null, 'manual' => 0],
            ['id' => 4, 'points' => 55.5, 'reached' => 200, 'isAnswered' => false, 'sequence' => null, 'manual' => 1]
        ];
        foreach ($expected as $value) {
            $this->testObj->addAnsweredQuestion(
                $value['id'],
                $value['points'],
                $value['reached'],
                $value['isAnswered'],
                $value['sequence'],
                $value['manual']
            );
        }

        foreach ($expected as $value) {
            $this->assertEquals($value, $this->testObj->getAnsweredQuestionByQuestionId($value['id']));
        }
    }

    public function testGetAnsweredQuestionCount(): void
    {
        $expected = [
            ['id' => 20, 'points' => 2.5, 'reached' => 1.5, 'isAnswered' => true, 'sequence' => null, 'manual' => 0],
            ['id' => 12, 'points' => 12.5, 'reached' => 11, 'isAnswered' => true, 'sequence' => null, 'manual' => 1],
            ['id' => 165, 'points' => -5.5, 'reached' => 0, 'isAnswered' => false, 'sequence' => null, 'manual' => 0],
            ['id' => 4, 'points' => 55.5, 'reached' => 200, 'isAnswered' => false, 'sequence' => null, 'manual' => 1]
        ];

        foreach ($expected as $value) {
            $this->testObj->addAnsweredQuestion(
                $value['id'],
                $value['points'],
                $value['reached'],
                $value['isAnswered'],
                $value['sequence'],
                $value['manual']
            );
        }

        $this->assertEquals(count($expected), $this->testObj->getAnsweredQuestionCount());
    }

    public function testRequestedHintsCount(): void
    {
        $requestedHintsCount = 5;
        $this->testObj->setRequestedHintsCount($requestedHintsCount);

        $this->assertEquals($requestedHintsCount, $this->testObj->getRequestedHintsCount());
    }

    public function testDeductedHintPoints(): void
    {
        $deductedHintPoints = 5;
        $this->testObj->setDeductedHintPoints($deductedHintPoints);

        $this->assertEquals($deductedHintPoints, $this->testObj->getDeductedHintPoints());
    }

    public function testObligationsAnswered(): void
    {
        $this->testObj->setObligationsAnswered(true);

        $this->assertTrue($this->testObj->areObligationsAnswered());
    }

    public function testExamId(): void
    {
        $exam_id = '5';
        $this->testObj->setExamId($exam_id);

        $this->assertEquals($exam_id, $this->testObj->getExamId());
    }
}
