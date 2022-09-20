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
 * Class ilTermsOfServiceAcceptanceEntityTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentEvaluationTest extends ilTermsOfServiceEvaluationBaseTest
{
    public function testAskingEvaluatorForDocumentExistenceIfNoDocumentExistAtAllResultsInANegativeAnswer(): void
    {
        $evaluator = $this->getEvaluatorMock();
        $user = $this->getUserMock();
        $log = $this->getLogMock();

        $evaluation = new ilTermsOfServiceSequentialDocumentEvaluation(
            $evaluator,
            $user,
            $log,
            []
        );

        $this->assertFalse($evaluation->hasDocument());
    }

    public function testExceptionIsRaisedIfADocumentIsRequestedFromEvaluatorAndNoDocumentExistsAtAll(): void
    {
        $evaluator = $this->getEvaluatorMock();
        $user = $this->getUserMock();
        $log = $this->getLogMock();

        $evaluation = new ilTermsOfServiceSequentialDocumentEvaluation(
            $evaluator,
            $user,
            $log,
            []
        );

        $this->expectException(ilTermsOfServiceNoSignableDocumentFoundException::class);

        $evaluation->document();
    }

    public function testFirstDocumentIsReturnedIfEvaluationOfFirstDocumentSucceeded(): void
    {
        $evaluator = $this->getEvaluatorMock();
        $user = $this->getUserMock();
        $log = $this->getLogMock();

        $doc = $this
            ->getMockBuilder(ilTermsOfServiceSignableDocument::class)
            ->getMock();

        $evaluator
            ->expects($this->once())
            ->method('evaluate')
            ->with($doc)
            ->willReturn(true);

        $evaluation = new ilTermsOfServiceSequentialDocumentEvaluation(
            $evaluator,
            $user,
            $log,
            [$doc]
        );

        $this->assertTrue($evaluation->hasDocument());
        $this->assertSame($doc, $evaluation->document());
    }

    public function testDocumentOnArbitraryPositionIsReturnedMatchingFirstDocumentWithASucceededEvaluation(): void
    {
        $evaluator = $this->getEvaluatorMock();
        $user = $this->getUserMock();
        $log = $this->getLogMock();

        $doc1 = $this
            ->getMockBuilder(ilTermsOfServiceSignableDocument::class)
            ->getMock();

        $doc2 = $this
            ->getMockBuilder(ilTermsOfServiceSignableDocument::class)
            ->getMock();

        $doc3 = $this
            ->getMockBuilder(ilTermsOfServiceSignableDocument::class)
            ->getMock();

        $evaluator
            ->expects($this->exactly(3))
            ->method('evaluate')
            ->withConsecutive(
                [$doc1],
                [$doc2],
                [$doc3]
            )
            ->willReturnMap([
                [$doc1, false],
                [$doc2, true],
                [$doc3, false],
            ]);

        $evaluation = new ilTermsOfServiceSequentialDocumentEvaluation(
            $evaluator,
            $user,
            $log,
            [$doc1, $doc2, $doc3]
        );

        $this->assertTrue($evaluation->hasDocument());
        $this->assertSame($doc2, $evaluation->document());
    }

    public function testFirstMatchingDocumentIsReturnedIfEvaluationOfMultipleDocumentsSucceeded(): void
    {
        $evaluator = $this->getEvaluatorMock();
        $user = $this->getUserMock();
        $log = $this->getLogMock();

        $doc1 = $this
            ->getMockBuilder(ilTermsOfServiceSignableDocument::class)
            ->getMock();

        $doc2 = $this
            ->getMockBuilder(ilTermsOfServiceSignableDocument::class)
            ->getMock();

        $doc3 = $this
            ->getMockBuilder(ilTermsOfServiceSignableDocument::class)
            ->getMock();

        $evaluator
            ->expects($this->exactly(3))
            ->method('evaluate')
            ->withConsecutive(
                [$doc1],
                [$doc2],
                [$doc3]
            )
            ->willReturnMap([
                [$doc1, false],
                [$doc2, true],
                [$doc3, true],
            ]);

        $evaluation = new ilTermsOfServiceSequentialDocumentEvaluation(
            $evaluator,
            $user,
            $log,
            [$doc1, $doc2, $doc3]
        );

        $this->assertTrue($evaluation->hasDocument());
        $this->assertSame($doc2, $evaluation->document());
    }
}
