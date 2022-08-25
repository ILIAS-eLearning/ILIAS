<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestRandomQuestionCollectionSubsetApplicationTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionCollectionSubsetApplicationTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionCollectionSubsetApplication $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionCollectionSubsetApplication();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestRandomQuestionCollectionSubsetApplication::class, $this->testObj);
    }

    public function testApplicantId(): void
    {
        $this->testObj->setApplicantId(12);
        $this->assertEquals(12, $this->testObj->getApplicantId());
    }

    public function testRequiredAmount(): void
    {
        $this->testObj->setRequiredAmount(12);
        $this->assertEquals(12, $this->testObj->getRequiredAmount());
    }

    public function testHasRequiredAmountLeft(): void
    {
        $this->testObj->setRequiredAmount(5);
        $this->assertTrue($this->testObj->hasRequiredAmountLeft());

        $this->testObj->setRequiredAmount(-200);
        $this->assertFalse($this->testObj->hasRequiredAmountLeft());

        $this->testObj->setRequiredAmount(0);
        $this->assertFalse($this->testObj->hasRequiredAmountLeft());
    }

    public function testDecrementRequiredAmount(): void
    {
        $this->testObj->setRequiredAmount(5);
        $this->testObj->decrementRequiredAmount();
        $this->assertEquals(4, $this->testObj->getRequiredAmount());
    }

    public function testHasQuestion(): void
    {
        $this->assertFalse($this->testObj->hasQuestion(2));

        $question = new ilTestRandomQuestionSetQuestion();
        $question->setQuestionId(2);

        $this->testObj->addQuestion($question);
        $this->assertTrue($this->testObj->hasQuestion(2));
    }

    public function testGetQuestion(): void
    {
        $question = new ilTestRandomQuestionSetQuestion();
        $question->setQuestionId(2);
        $this->testObj->addQuestion($question);

        $this->assertEquals($question, $this->testObj->getQuestion(2));
    }
}
