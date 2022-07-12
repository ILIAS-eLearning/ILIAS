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

use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ilTermsOfServiceEvaluationBaseTest
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilTermsOfServiceEvaluationBaseTest extends ilTermsOfServiceBaseTest
{
    /**
     * @return MockObject&ilObjUser
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
     * @return MockObject&ilLogger
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
     * @return MockObject&ilTermsOfServiceDocumentCriteriaEvaluation
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
     * @return MockObject&ilTermsOfServiceCriterionTypeFactoryInterface
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
     * @return MockObject&ilTermsOfServiceCriterionType
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
     * @return MockObject&ilTermsOfServiceEvaluableCriterion
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
