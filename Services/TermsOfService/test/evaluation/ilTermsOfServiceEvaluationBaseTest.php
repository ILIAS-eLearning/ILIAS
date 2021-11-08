<?php declare(strict_types=1);
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
     */
    protected function getUserMock() : ilObjUser
    {
        $user = $this
            ->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getLanguage', 'getId', 'getLogin'])
            ->getMock();

        $user
            ->method('getId')
            ->willReturn(-1);

        $user
            ->method('getLogin')
            ->willReturn('phpunit');

        return $user;
    }

    /**
     * @return MockObject|ilLogger
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
     */
    protected function getCriterionTypeMock(string $typeIdent) : ilTermsOfServiceCriterionType
    {
        $criterionType = $this
            ->getMockBuilder(ilTermsOfServiceCriterionType::class)
            ->getMock();

        $criterionType
            ->method('getTypeIdent')
            ->willReturn($typeIdent);

        return $criterionType;
    }

    /**
     * @param ilTermsOfServiceCriterionType $criterionType
     * @return MockObject|ilTermsOfServiceEvaluableCriterion
     */
    protected function getCriterionAssignmentMock(
        ilTermsOfServiceCriterionType $criterionType
    ) : ilTermsOfServiceEvaluableCriterion {
        $criterionAssignment = $this
            ->getMockBuilder(ilTermsOfServiceEvaluableCriterion::class)
            ->getMock();

        $criterionAssignment
            ->method('getCriterionId')
            ->willReturn($criterionType->getTypeIdent());

        return $criterionAssignment;
    }
}
