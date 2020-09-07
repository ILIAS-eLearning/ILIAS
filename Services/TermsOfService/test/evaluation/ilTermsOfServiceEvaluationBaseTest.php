<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceEvaluationBaseTest
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilTermsOfServiceEvaluationBaseTest extends \ilTermsOfServiceBaseTest
{
    /**
     * @return PHPUnit_Framework_MockObject_MockObject|\ilObjUser
     */
    protected function getUserMock() : \ilObjUser
    {
        $user = $this
            ->getMockBuilder(\ilObjUser::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLanguage', 'getId', 'getLogin'])
            ->getMock();

        $user
            ->expects($this->any())
            ->method('getId')
            ->willReturn(-1);

        $user
            ->expects($this->any())
            ->method('getLogin')
            ->willReturn('phpunit');

        return $user;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|\ilLogger
     */
    protected function getLogMock() : \ilLogger
    {
        $log = $this
            ->getMockBuilder(\ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $log;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|\ilTermsOfServiceDocumentCriteriaEvaluation
     */
    protected function getEvaluatorMock() : \ilTermsOfServiceDocumentCriteriaEvaluation
    {
        $evaluator = $this
            ->getMockBuilder(\ilTermsOfServiceDocumentCriteriaEvaluation::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $evaluator;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|\ilTermsOfServiceCriterionTypeFactoryInterface
     */
    protected function getCriterionTypeFactoryMock() : \ilTermsOfServiceCriterionTypeFactoryInterface
    {
        $criterionTypeFactory = $this
            ->getMockBuilder(\ilTermsOfServiceCriterionTypeFactoryInterface::class)
            ->getMock();

        return $criterionTypeFactory;
    }

    /**
     * @param string $typeIdent
     * @return PHPUnit_Framework_MockObject_MockObject|\ilTermsOfServiceCriterionType
     */
    protected function getCriterionTypeMock(string $typeIdent) : \ilTermsOfServiceCriterionType
    {
        $criterionType = $this
            ->getMockBuilder(\ilTermsOfServiceCriterionType::class)
            ->getMock();

        $criterionType
            ->expects($this->any())
            ->method('getTypeIdent')
            ->willReturn($typeIdent);

        return $criterionType;
    }

    /**
     * @param ilTermsOfServiceCriterionType $criterionType
     * @return PHPUnit_Framework_MockObject_MockObject|\ilTermsOfServiceEvaluableCriterion
     */
    protected function getCriterionAssignmentMock(\ilTermsOfServiceCriterionType $criterionType) : \ilTermsOfServiceEvaluableCriterion
    {
        $criterionAssignment = $this
            ->getMockBuilder(\ilTermsOfServiceEvaluableCriterion::class)
            ->getMock();

        $criterionAssignment
            ->expects($this->any())
            ->method('getCriterionId')
            ->willReturn($criterionType->getTypeIdent());

        return $criterionAssignment;
    }
}
