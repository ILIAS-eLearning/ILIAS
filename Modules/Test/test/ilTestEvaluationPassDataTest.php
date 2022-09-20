<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

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
            "answeredQuestions",
            "pass",
            "nrOfAnsweredQuestions",
            "reachedpoints",
            "maxpoints",
            "questioncount",
            "workingtime",
            "examId"
        ];
        $this->assertEquals($expected, $this->testObj->__sleep());
    }

    public function testNrOfAnsweredQuestions(): void
    {
        $this->testObj->setNrOfAnsweredQuestions(20);
        $this->assertEquals(20, $this->testObj->getNrOfAnsweredQuestions());
    }

    public function testReachedPoints(): void
    {
        $this->testObj->setReachedPoints(20);
        $this->assertEquals(20, $this->testObj->getReachedPoints());
    }

    public function testMaxPoints(): void
    {
        $this->testObj->setMaxPoints(20);
        $this->assertEquals(20, $this->testObj->getMaxPoints());
    }

    public function testQuestionCount(): void
    {
        $this->testObj->setQuestionCount(20);
        $this->assertEquals(20, $this->testObj->getQuestionCount());
    }

    public function testWorkingTime(): void
    {
        $this->testObj->setWorkingTime(20);
        $this->assertEquals(20, $this->testObj->getWorkingTime());
    }

    public function testPass(): void
    {
        $this->testObj->setPass(20);
        $this->assertEquals(20, $this->testObj->getPass());
    }

    public function testAnsweredQuestions(): void
    {
        $expected = [
            ["id" => 20, "points" => 2.5, "reached" => 1.5, "isAnswered" => true, "sequence" => null, "manual" => 0],
            ["id" => 12, "points" => 12.5, "reached" => 11, "isAnswered" => true, "sequence" => null, "manual" => 1],
            ["id" => 165, "points" => -5.5, "reached" => 0, "isAnswered" => false, "sequence" => null, "manual" => 0],
            ["id" => 4, "points" => 55.5, "reached" => 200, "isAnswered" => false, "sequence" => null, "manual" => 1],
        ];

        foreach ($expected as $value) {
            $this->testObj->addAnsweredQuestion(
                $value["id"],
                $value["points"],
                $value["reached"],
                $value["isAnswered"],
                $value["sequence"],
                $value["manual"],
            );
        }

        $this->assertEquals($expected, $this->testObj->getAnsweredQuestions());

        $this->assertEquals($expected[1], $this->testObj->getAnsweredQuestion(1));
    }

    public function testGetAnsweredQuestionByQuestionId(): void
    {
        $expected = [
            ["id" => 20, "points" => 2.5, "reached" => 1.5, "isAnswered" => true, "sequence" => null, "manual" => 0],
            ["id" => 12, "points" => 12.5, "reached" => 11, "isAnswered" => true, "sequence" => null, "manual" => 1],
            ["id" => 165, "points" => -5.5, "reached" => 0, "isAnswered" => false, "sequence" => null, "manual" => 0],
            ["id" => 4, "points" => 55.5, "reached" => 200, "isAnswered" => false, "sequence" => null, "manual" => 1],
        ];

        foreach ($expected as $value) {
            $this->testObj->addAnsweredQuestion(
                $value["id"],
                $value["points"],
                $value["reached"],
                $value["isAnswered"],
                $value["sequence"],
                $value["manual"],
            );
        }

        $this->assertEquals($expected[1], $this->testObj->getAnsweredQuestionByQuestionId(12));
    }

    public function testGetAnsweredQuestionCount(): void
    {
        $expected = [
            ["id" => 20, "points" => 2.5, "reached" => 1.5, "isAnswered" => true, "sequence" => null, "manual" => 0],
            ["id" => 12, "points" => 12.5, "reached" => 11, "isAnswered" => true, "sequence" => null, "manual" => 1],
            ["id" => 165, "points" => -5.5, "reached" => 0, "isAnswered" => false, "sequence" => null, "manual" => 0],
            ["id" => 4, "points" => 55.5, "reached" => 200, "isAnswered" => false, "sequence" => null, "manual" => 1],
        ];

        foreach ($expected as $value) {
            $this->testObj->addAnsweredQuestion(
                $value["id"],
                $value["points"],
                $value["reached"],
                $value["isAnswered"],
                $value["sequence"],
                $value["manual"],
            );
        }

        $this->assertEquals(4, $this->testObj->getAnsweredQuestionCount());
    }

    public function testRequestedHintsCount(): void
    {
        $this->testObj->setRequestedHintsCount(5);

        $this->assertEquals(5, $this->testObj->getRequestedHintsCount());
    }

    public function testDeductedHintPoints(): void
    {
        $this->testObj->setDeductedHintPoints(5);

        $this->assertEquals(5, $this->testObj->getDeductedHintPoints());
    }

    public function testObligationsAnswered(): void
    {
        $this->testObj->setObligationsAnswered(true);

        $this->assertTrue($this->testObj->areObligationsAnswered());
    }

    public function testExamId(): void
    {
        $this->testObj->setExamId("5");

        $this->assertEquals("5", $this->testObj->getExamId());
    }
}
