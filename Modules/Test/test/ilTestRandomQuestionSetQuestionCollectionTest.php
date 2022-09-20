<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestRandomQuestionSetQuestionCollectionTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetQuestionCollectionTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetQuestionCollection $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionSetQuestionCollection();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetQuestionCollection::class, $this->testObj);
    }

    public function testQuestions(): void
    {
        $expected = [
            new ilTestRandomQuestionSetQuestion(),
            new ilTestRandomQuestionSetQuestion()
        ];

        $this->testObj->setQuestions($expected);
        $this->assertEquals($expected, $this->testObj->getQuestions());
    }

    public function testAddQuestions(): void
    {
        $this->testObj->addQuestion(new ilTestRandomQuestionSetQuestion());
        $this->testObj->addQuestion(new ilTestRandomQuestionSetQuestion());
        $this->testObj->addQuestion(new ilTestRandomQuestionSetQuestion());
        $this->assertCount(3, $this->testObj->getQuestions());
    }

    public function testCurrent(): void
    {
        $questions = [];
        $ids = [125, 112, 10];
        foreach ($ids as $id) {
            $question = new ilTestRandomQuestionSetQuestion();
            $question->setQuestionId($id);
            $questions[] = $question;
        }

        $this->testObj->setQuestions($questions);

        $this->assertEquals($questions[0], $this->testObj->current());
    }

    public function testNext(): void
    {
        $questions = [];
        $ids = [125, 112, 10];
        foreach ($ids as $id) {
            $question = new ilTestRandomQuestionSetQuestion();
            $question->setQuestionId($id);
            $questions[] = $question;
        }

        $this->testObj->setQuestions($questions);

        $this->assertEquals($questions[1], $this->testObj->next());
    }

    public function testKey(): void
    {
        $questions = [];
        $ids = [125, 112, 10];
        foreach ($ids as $id) {
            $question = new ilTestRandomQuestionSetQuestion();
            $question->setQuestionId($id);
            $questions[] = $question;
        }

        $this->testObj->setQuestions($questions);

        $this->assertEquals(0, $this->testObj->key());

        $this->testObj->next();
        $this->testObj->next();
        $this->assertEquals(2, $this->testObj->key());
    }

    public function testValid(): void
    {
        $questions = [];
        $ids = [125, 112, 10];
        foreach ($ids as $id) {
            $question = new ilTestRandomQuestionSetQuestion();
            $question->setQuestionId($id);
            $questions[] = $question;
        }

        $this->testObj->setQuestions($questions);

        $this->assertTrue($this->testObj->valid());

        $this->testObj->setQuestions([]);
        $this->assertFalse($this->testObj->valid());
    }

    public function testRewind(): void
    {
        $questions = [];
        $ids = [125, 112, 10];
        foreach ($ids as $id) {
            $question = new ilTestRandomQuestionSetQuestion();
            $question->setQuestionId($id);
            $questions[] = $question;
        }

        $this->testObj->setQuestions($questions);

        $this->assertEquals($questions[0], $this->testObj->current());

        $this->testObj->next();
        $this->testObj->next();
        $this->assertEquals($questions[2], $this->testObj->current());

        $this->testObj->rewind();
        $this->assertEquals($questions[0], $this->testObj->current());
    }

    public function testIsGreaterThan(): void
    {
        $questions = [];
        $ids = [125, 112, 10];
        foreach ($ids as $id) {
            $question = new ilTestRandomQuestionSetQuestion();
            $question->setQuestionId($id);
            $questions[] = $question;
        }

        $this->testObj->setQuestions($questions);

        $this->assertTrue($this->testObj->isGreaterThan(2));
        $this->assertTrue($this->testObj->isGreaterThan(1));
        $this->assertFalse($this->testObj->isGreaterThan(6));
    }

    public function testIsSmallerThan(): void
    {
        $questions = [];
        $ids = [125, 112, 10];
        foreach ($ids as $id) {
            $question = new ilTestRandomQuestionSetQuestion();
            $question->setQuestionId($id);
            $questions[] = $question;
        }

        $this->testObj->setQuestions($questions);

        $this->assertFalse($this->testObj->isSmallerThan(3));
        $this->assertFalse($this->testObj->isSmallerThan(1));
        $this->assertTrue($this->testObj->isSmallerThan(6));
    }

    public function testGetMissingCount(): void
    {
        $questions = [];
        $ids = [125, 112, 10];
        foreach ($ids as $id) {
            $question = new ilTestRandomQuestionSetQuestion();
            $question->setQuestionId($id);
            $questions[] = $question;
        }

        $this->testObj->setQuestions($questions);

        $this->assertEquals(0, $this->testObj->getMissingCount(3));
        $this->assertEquals(0, $this->testObj->getMissingCount(1));
        $this->assertEquals(3, $this->testObj->getMissingCount(6));
    }

    public function testMergeQuestionCollection(): void
    {
        $questions = [];
        $ids = [125, 112, 10];
        foreach ($ids as $index => $id) {
            $question = new ilTestRandomQuestionSetQuestion();
            $question->setQuestionId($id);
            $questions[$index] = $question;
            $this->testObj->addQuestion($question);
        }

        $collection = new ilTestRandomQuestionSetQuestionCollection();

        $ids = [1, 5, 8];
        foreach ($ids as $index => $id) {
            $question = new ilTestRandomQuestionSetQuestion();
            $question->setQuestionId($id);
            $questions[$index + 3] = $question;
            $collection->addQuestion($question);
        }

        $this->testObj->mergeQuestionCollection($collection);

        $this->assertEquals($questions, $this->testObj->getQuestions());
    }

    public function testGetUniqueQuestionCollection(): void
    {
        $ids = [125, 112, 10, 112];
        foreach ($ids as $id) {
            $question = new ilTestRandomQuestionSetQuestion();
            $question->setQuestionId($id);
            $this->testObj->addQuestion($question);
        }

        $this->assertCount(3, $this->testObj->getUniqueQuestionCollection()->getQuestions());
    }

    public function testGetQuestionAmount(): void
    {
        $ids = [125, 112, 10];
        foreach ($ids as $id) {
            $question = new ilTestRandomQuestionSetQuestion();
            $question->setQuestionId($id);
            $this->testObj->addQuestion($question);
        }

        $this->assertEquals(3, $this->testObj->getQuestionAmount());
    }

    public function testGetInvolvedQuestionIds(): void
    {
        $ids = [125, 112, 10];
        foreach ($ids as $id) {
            $question = new ilTestRandomQuestionSetQuestion();
            $question->setQuestionId($id);
            $this->testObj->addQuestion($question);
        }

        $this->assertEquals($ids, $this->testObj->getInvolvedQuestionIds());
    }
}
