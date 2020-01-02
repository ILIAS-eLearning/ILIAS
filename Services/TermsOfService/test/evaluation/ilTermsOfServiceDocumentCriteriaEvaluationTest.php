<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceDocumentCriteriaEvaluationTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentCriteriaEvaluationTest extends \ilTermsOfServiceEvaluationBaseTest
{
    /**
     *
     */
    public function testLogicalAndEvaluatorReturnsTrueIfNoCriterionIsAttachedToADocumentAtAll()
    {
        $user = $this->getUserMock();
        $criterionTypeFactory = $this->getCriterionTypeFactoryMock();
        $log = $this->getLogMock();

        $doc = $this
            ->getMockBuilder(\ilTermsOfServiceSignableDocument::class)
            ->getMock();

        $doc
            ->expects($this->once())
            ->method('criteria')
            ->willReturn([]);

        $evaluator = new \ilTermsOfServiceLogicalAndDocumentCriteriaEvaluation($criterionTypeFactory, $user, $log);

        $this->assertTrue($evaluator->evaluate($doc));
    }

    /**
     *
     */
    public function testLogicalAndEvaluatorReturnsTrueIfAllCriteriaAttachedToADocumentMatch()
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
            ->getMockBuilder(\ilTermsOfServiceSignableDocument::class)
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

        $evaluator = new \ilTermsOfServiceLogicalAndDocumentCriteriaEvaluation($criterionTypeFactory, $user, $log);

        $this->assertTrue($evaluator->evaluate($doc));
    }

    /**
     *
     */
    public function testLogicalAndEvaluatorReturnsFalseIfAnyCriteriaAttachedToADocumentDoesNotMatch()
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
            ->getMockBuilder(\ilTermsOfServiceSignableDocument::class)
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

        $evaluator = new \ilTermsOfServiceLogicalAndDocumentCriteriaEvaluation($criterionTypeFactory, $user, $log);

        $this->assertFalse($evaluator->evaluate($doc));
    }
}
