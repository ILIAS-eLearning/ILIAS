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
        $applicantId = 12;
        $this->testObj->setApplicantId($applicantId);
        $this->assertEquals($applicantId, $this->testObj->getApplicantId());
    }

    public function testRequiredAmount(): void
    {
        $requiredAmount = 12;
        $this->testObj->setRequiredAmount($requiredAmount);
        $this->assertEquals($requiredAmount, $this->testObj->getRequiredAmount());
    }

    public function testHasRequiredAmountLeft(): void
    {
        for ($i = 3; $i > -3; $i--) {
            $this->testObj->setRequiredAmount($i);
            $this->assertEquals($i > 0, $this->testObj->hasRequiredAmountLeft());
        }
    }

    public function testDecrementRequiredAmount(): void
    {
        $requiredAmount = 5;
        $this->testObj->setRequiredAmount($requiredAmount);
        $this->testObj->decrementRequiredAmount();
        $this->assertEquals(--$requiredAmount, $this->testObj->getRequiredAmount());
    }

    public function testHasQuestion(): void
    {
        $questionId = 2;
        $this->assertFalse($this->testObj->hasQuestion($questionId));

        $question = new ilTestRandomQuestionSetQuestion();
        $question->setQuestionId($questionId);

        $this->testObj->addQuestion($question);
        $this->assertTrue($this->testObj->hasQuestion($questionId));
    }

    public function testGetQuestion(): void
    {
        $questionId = 2;
        $question = new ilTestRandomQuestionSetQuestion();
        $question->setQuestionId($questionId);
        $this->testObj->addQuestion($question);

        $this->assertEquals($question, $this->testObj->getQuestion($questionId));
    }
}
