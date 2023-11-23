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
            new ilTestRandomQuestionSetQuestion(),
        ];

        $this->testObj->setQuestions($expected);
        $this->assertEquals($expected, $this->testObj->getQuestions());
    }

    public function testAddQuestions(): void
    {
        for ($count = 0; $count < 3; $count++) {
            $this->testObj->addQuestion(new ilTestRandomQuestionSetQuestion());
        }
        $this->assertCount($count, $this->testObj->getQuestions());
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

        $iMax = count($ids);
        if ($iMax > 0) {
            $this->assertEquals(0, $this->testObj->key());

            for ($i = 1; $i < $iMax; $i++) {
                $this->testObj->next();
            }

            $this->assertEquals(--$i, $this->testObj->key());
        } else {
            $this->assertTrue(false);
        }
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

        $iMax = count($questions);
        if ($iMax > 0) {
            $this->assertEquals($questions[0], $this->testObj->current());

            for ($i = 1; $i < $iMax; $i++) {
                $this->testObj->next();
            }

            $this->assertEquals($questions[--$i], $this->testObj->current());

            for ($j = $i; $j > 0; $j--) {
                $this->testObj->rewind();
            }

            $this->assertEquals($questions[$j], $this->testObj->current());
        } else {
            $this->assertTrue(false);
        }
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

        for ($i = 0, $iMax = count($questions); $i < $iMax; $i++) {
            $this->assertTrue($this->testObj->isGreaterThan($i));
        }
        $this->assertFalse($this->testObj->isGreaterThan($i));
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

        for ($questionsCount = count($questions), $i = $questionsCount * 2; $i > $questionsCount; $i--) {
            $this->assertTrue($this->testObj->isSmallerThan($i));
        }
        $this->assertFalse($this->testObj->isSmallerThan($i));
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

        $questionsCount = count($questions);

        for ($i = 0, $iMax = $questionsCount; $i < $iMax; $i++) {
            $this->assertEquals(0, $this->testObj->getMissingCount($i));
        }

        for ($i = $questionsCount, $iMax = $questionsCount * 2; $i <= $iMax; $i++) {
            $this->assertEquals($i - $questionsCount, $this->testObj->getMissingCount($i));
        }
    }

    public function testMergeQuestionCollection(): void
    {
        $questions = [];
        $ids = [125, 112, 10];
        foreach ($ids as $id) {
            $question = new ilTestRandomQuestionSetQuestion();
            $question->setQuestionId($id);
            $questions[] = $question;
            $this->testObj->addQuestion($question);
        }

        $collection = new ilTestRandomQuestionSetQuestionCollection();

        $ids = [1, 5, 8];
        foreach ($ids as $id) {
            $question = new ilTestRandomQuestionSetQuestion();
            $question->setQuestionId($id);
            $questions[] = $question;
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

        $this->assertCount(count(array_unique($ids)), $this->testObj->getUniqueQuestionCollection()->getQuestions());
    }

    public function testGetQuestionAmount(): void
    {
        $ids = [125, 112, 10];
        foreach ($ids as $id) {
            $question = new ilTestRandomQuestionSetQuestion();
            $question->setQuestionId($id);
            $this->testObj->addQuestion($question);
        }

        $this->assertEquals(count($ids), $this->testObj->getQuestionAmount());
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
