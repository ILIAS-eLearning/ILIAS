<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ilTermsOfServiceEvaluationBaseTest
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilTermsOfServiceEvaluationBaseTest extends ilTermsOfServiceBaseTest
{
    /**
     * @return MockObject|ilObjUser
     * @throws ReflectionException
     */
    protected function getUserMock() : ilObjUser
    {
        $user = $this
            ->getMockBuilder(ilObjUser::class)
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
     * @return MockObject|ilLogger
     * @throws ReflectionException
     */
    protected function getLogMock() : ilLogger
    {
        $log = $this
            ->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $log;
    }

    /**
     * @return MockObject|ilTermsOfServiceDocumentCriteriaEvaluation
     * @throws ReflectionException
     */
    protected function getEvaluatorMock() : ilTermsOfServiceDocumentCriteriaEvaluation
    {
        $evaluator = $this
            ->getMockBuilder(ilTermsOfServiceDocumentCriteriaEvaluation::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $evaluator;
    }

    /**
     * @return MockObject|ilTermsOfServiceCriterionTypeFactoryInterface
     * @throws ReflectionException
     */
    protected function getCriterionTypeFactoryMock() : ilTermsOfServiceCriterionTypeFactoryInterface
    {
        $criterionTypeFactory = $this
            ->getMockBuilder(ilTermsOfServiceCriterionTypeFactoryInterface::class)
            ->getMock();

        return $criterionTypeFactory;
    }

    /**
     * @param string $typeIdent
     * @return MockObject|ilTermsOfServiceCriterionType
     * @throws ReflectionException
     */
    protected function getCriterionTypeMock(string $typeIdent) : ilTermsOfServiceCriterionType
    {
        $criterionType = $this
            ->getMockBuilder(ilTermsOfServiceCriterionType::class)
            ->getMock();

        $criterionType
            ->expects($this->any())
            ->method('getTypeIdent')
            ->willReturn($typeIdent);

        return $criterionType;
    }

    /**
     * @param ilTermsOfServiceCriterionType $criterionType
     * @return MockObject|ilTermsOfServiceEvaluableCriterion
     * @throws ReflectionException
     */
    protected function getCriterionAssignmentMock(
        ilTermsOfServiceCriterionType $criterionType
    ) : ilTermsOfServiceEvaluableCriterion {
        $criterionAssignment = $this
            ->getMockBuilder(ilTermsOfServiceEvaluableCriterion::class)
            ->getMock();

        $criterionAssignment
            ->expects($this->any())
            ->method('getCriterionId')
            ->willReturn($criterionType->getTypeIdent());

        return $criterionAssignment;
    }
}