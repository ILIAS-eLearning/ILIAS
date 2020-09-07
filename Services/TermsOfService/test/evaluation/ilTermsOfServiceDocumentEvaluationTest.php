<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceAcceptanceEntityTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentEvaluationTest extends \ilTermsOfServiceEvaluationBaseTest
{
    /**
     *
     */
    public function testAskingEvaluatorForDocumentExistenceIfNoDocumentExistAtAllResultsInANegativeAnswer()
    {
        $evaluator = $this->getEvaluatorMock();
        $user = $this->getUserMock();
        $log = $this->getLogMock();

        $evaluation = new \ilTermsOfServiceSequentialDocumentEvaluation(
            $evaluator,
            $user,
            $log,
            []
        );

        $this->assertFalse($evaluation->hasDocument());
    }

    /**
     * @expectedException \ilTermsOfServiceNoSignableDocumentFoundException
     */
    public function testExceptionIsRaisedIfADocumentIsRequestedFromEvaluatorAndNoDocumentExistsAtAll()
    {
        $evaluator = $this->getEvaluatorMock();
        $user = $this->getUserMock();
        $log = $this->getLogMock();

        $evaluation = new \ilTermsOfServiceSequentialDocumentEvaluation(
            $evaluator,
            $user,
            $log,
            []
        );

        $this->assertException(\ilTermsOfServiceNoSignableDocumentFoundException::class);

        $evaluation->document();
    }

    /**
     *
     */
    public function testFirstDocumentIsReturnedIfEvaluationOfFirstDocumentSucceeded()
    {
        $evaluator = $this->getEvaluatorMock();
        $user = $this->getUserMock();
        $log = $this->getLogMock();

        $doc = $this
            ->getMockBuilder(\ilTermsOfServiceSignableDocument::class)
            ->getMock();

        $evaluator
            ->expects($this->exactly(1))
            ->method('evaluate')
            ->with($doc)
            ->willReturn(true);

        $evaluation = new \ilTermsOfServiceSequentialDocumentEvaluation(
            $evaluator,
            $user,
            $log,
            [$doc]
        );

        $this->assertTrue($evaluation->hasDocument());
        $this->assertEquals($doc, $evaluation->document());
    }

    /**
     *
     */
    public function testDocumentOnArbitraryPositionIsReturnedMatchingFirstDocumentWithASucceededEvaluation()
    {
        $evaluator = $this->getEvaluatorMock();
        $user = $this->getUserMock();
        $log = $this->getLogMock();

        $doc1 = $this
            ->getMockBuilder(\ilTermsOfServiceSignableDocument::class)
            ->getMock();

        $doc2 = $this
            ->getMockBuilder(\ilTermsOfServiceSignableDocument::class)
            ->getMock();

        $doc3 = $this
            ->getMockBuilder(\ilTermsOfServiceSignableDocument::class)
            ->getMock();

        $evaluator
            ->expects($this->exactly(3))
            ->method('evaluate')
            ->withConsecutive(
                [$doc1],
                [$doc2],
                [$doc3]
            )
            ->will($this->returnValueMap([
                [$doc1, false],
                [$doc2, true],
                [$doc3, false]
            ]));

        $evaluation = new \ilTermsOfServiceSequentialDocumentEvaluation(
            $evaluator,
            $user,
            $log,
            [$doc1, $doc2, $doc3]
        );

        $this->assertTrue($evaluation->hasDocument());
        $this->assertEquals($doc2, $evaluation->document());
    }

    /**
     *
     */
    public function testFirstMatchingDocumentIsReturnedIfEvaluationOfMultipleDocumentsSucceeded()
    {
        $evaluator = $this->getEvaluatorMock();
        $user = $this->getUserMock();
        $log = $this->getLogMock();

        $doc1 = $this
            ->getMockBuilder(\ilTermsOfServiceSignableDocument::class)
            ->getMock();

        $doc2 = $this
            ->getMockBuilder(\ilTermsOfServiceSignableDocument::class)
            ->getMock();

        $doc3 = $this
            ->getMockBuilder(\ilTermsOfServiceSignableDocument::class)
            ->getMock();

        $evaluator
            ->expects($this->exactly(3))
            ->method('evaluate')
            ->withConsecutive(
                [$doc1],
                [$doc2],
                [$doc3]
            )
            ->will($this->returnValueMap([
                [$doc1, false],
                [$doc2, true],
                [$doc3, true]
            ]));

        $evaluation = new \ilTermsOfServiceSequentialDocumentEvaluation(
            $evaluator,
            $user,
            $log,
            [$doc1, $doc2, $doc3]
        );

        $this->assertTrue($evaluation->hasDocument());
        $this->assertEquals($doc2, $evaluation->document());
    }
}
