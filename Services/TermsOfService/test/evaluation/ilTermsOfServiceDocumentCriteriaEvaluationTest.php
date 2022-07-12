<?php declare(strict_types=1);

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
 * Class ilTermsOfServiceDocumentCriteriaEvaluationTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentCriteriaEvaluationTest extends ilTermsOfServiceEvaluationBaseTest
{
    public function testLogicalAndEvaluatorReturnsTrueIfNoCriterionIsAttachedToADocumentAtAll() : void
    {
        $user = $this->getUserMock();
        $criterionTypeFactory = $this->getCriterionTypeFactoryMock();
        $log = $this->getLogMock();

        $doc = $this
            ->getMockBuilder(ilTermsOfServiceSignableDocument::class)
            ->getMock();

        $doc
            ->expects($this->once())
            ->method('criteria')
            ->willReturn([]);

        $evaluator = new ilTermsOfServiceLogicalAndDocumentCriteriaEvaluation($criterionTypeFactory, $user, $log);

        $this->assertTrue($evaluator->evaluate($doc));
    }

    public function testLogicalAndEvaluatorReturnsTrueIfAllCriteriaAttachedToADocumentMatch() : void
    {
        $user = $this->getUserMock();
        $log = $this->getLogMock();

        $criterionTypeFactory = $this->getCriterionTypeFactoryMock();

        $criterionType1 = $this->getCriterionTypeMock('dummy1');
        $criterionAssignment1 = $this->getCriterionAssignmentMock($criterionType1);

        $criterionType2 = $this->getCriterionTypeMock('dummy2');
        $criterionAssignment2 = $this->getCriterionAssignmentMock($criterionType2);

        $criterionType3 = $this->getCriterionTypeMock('dummy3');
        $criterionAssignment3 = $this->getCriterionAssignmentMock($criterionType3);

        $criterionType1
            ->expects($this->once())
            ->method('evaluate')
            ->with($user)
            ->willReturn(true);

        $criterionType2
            ->expects($this->once())
            ->method('evaluate')
            ->with($user)
            ->willReturn(true);

        $criterionType3
            ->expects($this->once())
            ->method('evaluate')
            ->with($user)
            ->willReturn(true);

        $doc = $this
            ->getMockBuilder(ilTermsOfServiceSignableDocument::class)
            ->getMock();

        $doc
            ->expects($this->once())
            ->method('criteria')
            ->willReturn([
                $criterionAssignment1,
                $criterionAssignment2,
                $criterionAssignment3
            ]);

        $criterionTypeFactory
            ->expects($this->exactly(3))
            ->method('findByTypeIdent')
            ->willReturnOnConsecutiveCalls(
                $criterionType1,
                $criterionType2,
                $criterionType3
            );

        $evaluator = new ilTermsOfServiceLogicalAndDocumentCriteriaEvaluation($criterionTypeFactory, $user, $log);

        $this->assertTrue($evaluator->evaluate($doc));
    }

    public function testLogicalAndEvaluatorReturnsFalseIfAnyCriteriaAttachedToADocumentDoesNotMatch() : void
    {
        $user = $this->getUserMock();
        $log = $this->getLogMock();

        $criterionTypeFactory = $this->getCriterionTypeFactoryMock();

        $criterionType1 = $this->getCriterionTypeMock('dummy1');
        $criterionAssignment1 = $this->getCriterionAssignmentMock($criterionType1);

        $criterionType2 = $this->getCriterionTypeMock('dummy2');
        $criterionAssignment2 = $this->getCriterionAssignmentMock($criterionType2);

        $criterionType3 = $this->getCriterionTypeMock('dummy3');
        $criterionAssignment3 = $this->getCriterionAssignmentMock($criterionType3);

        $criterionType1
            ->expects($this->once())
            ->method('evaluate')
            ->with($user)
            ->willReturn(true);

        $criterionType2
            ->expects($this->once())
            ->method('evaluate')
            ->with($user)
            ->willReturn(false);

        $criterionType3
            ->expects($this->never())
            ->method('evaluate')
            ->with($user)
            ->willReturn(true);

        $doc = $this
            ->getMockBuilder(ilTermsOfServiceSignableDocument::class)
            ->getMock();

        $doc
            ->expects($this->once())
            ->method('criteria')
            ->willReturn([
                $criterionAssignment1,
                $criterionAssignment2,
                $criterionAssignment3
            ]);

        $criterionTypeFactory
            ->expects($this->exactly(2))
            ->method('findByTypeIdent')
            ->willReturnOnConsecutiveCalls(
                $criterionType1,
                $criterionType2,
                $criterionType3
            );

        $evaluator = new ilTermsOfServiceLogicalAndDocumentCriteriaEvaluation($criterionTypeFactory, $user, $log);

        $this->assertFalse($evaluator->evaluate($doc));
    }

    public function testMutatingTheContextUserResultsInANewInstance() : void
    {
        $evaluator = new ilTermsOfServiceLogicalAndDocumentCriteriaEvaluation(
            $this->getCriterionTypeFactoryMock(),
            $this->getUserMock(),
            $this->getLogMock()
        );

        $this->assertNotSame($evaluator, $evaluator->withContextUser($this->getUserMock()));
    }
}
