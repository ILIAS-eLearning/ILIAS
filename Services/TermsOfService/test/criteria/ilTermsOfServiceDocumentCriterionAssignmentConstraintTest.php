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

use ILIAS\Data\Factory;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ilTermsOfServiceDocumentCriterionAssignmentConstraintTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentCriterionAssignmentConstraintTest extends ilTermsOfServiceCriterionBaseTest
{
    /**
     * @return MockObject&ilTermsOfServiceCriterionTypeFactoryInterface
     */
    protected function getCriterionTypeFactoryMock(): ilTermsOfServiceCriterionTypeFactoryInterface
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
    protected function getCriterionTypeMock(string $typeIdent): ilTermsOfServiceCriterionType
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
     * @return MockObject&ilTermsOfServiceCriterionTypeFactoryInterface
     */
    protected function getTypeMockForConstraint(): ilTermsOfServiceCriterionTypeFactoryInterface
    {
        $criterionTypeFactory = $this->getCriterionTypeFactoryMock();

        $criterionType1 = $this->getCriterionTypeMock('dummy');

        $criterionType1
            ->method('hasUniqueNature')
            ->willReturn(false);

        $criterionTypeFactory
            ->method('getTypesByIdentMap')
            ->willReturn([
                $criterionType1->getTypeIdent() => $criterionType1,
            ]);

        $criterionTypeFactory
            ->method('findByTypeIdent')
            ->willReturn($criterionType1);

        return $criterionTypeFactory;
    }

    public function criteriaAssignmentProvider(): array
    {
        $criterionAssignment1 = $this
            ->getMockBuilder(ilTermsOfServiceDocumentCriterionAssignment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCriterionValue', 'getCriterionId'])
            ->addMethods(['getId'])
            ->getMock();

        $criterionAssignment1
            ->method('getId')
            ->willReturn(1);

        $criterionAssignment1
            ->method('getCriterionId')
            ->willReturn('usr_global_role');

        $criterionAssignment1
            ->method('getCriterionValue')
            ->willReturn($this->getCriterionConfig(['role_id' => 4]));

        $criterionAssignment2 = $this
            ->getMockBuilder(ilTermsOfServiceDocumentCriterionAssignment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCriterionValue', 'getCriterionId'])
            ->addMethods(['getId'])
            ->getMock();

        $criterionAssignment2
            ->method('getId')
            ->willReturn(1);

        $criterionAssignment2
            ->method('getCriterionId')
            ->willReturn('usr_language');

        $criterionAssignment2
            ->method('getCriterionValue')
            ->willReturn($this->getCriterionConfig(['lng' => 'de']));

        $criterionAssignment3 = $this
            ->getMockBuilder(ilTermsOfServiceDocumentCriterionAssignment::class)
            ->onlyMethods(['getCriterionValue', 'getCriterionId'])
            ->addMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();

        $criterionAssignment3
            ->method('getId')
            ->willReturn(0);

        $criterionAssignment3
            ->method('getCriterionId')
            ->willReturn('usr_language');

        $criterionAssignment3
            ->method('getCriterionValue')
            ->willReturn($this->getCriterionConfig(['lng' => 'de']));

        $criterionAssignment4 = $this
            ->getMockBuilder(ilTermsOfServiceDocumentCriterionAssignment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCriterionValue', 'getCriterionId'])
            ->addMethods(['getId'])
            ->getMock();

        $criterionAssignment4
            ->method('getId')
            ->willReturn(0);

        $criterionAssignment4
            ->method('getCriterionId')
            ->willReturn('usr_global_role');

        $criterionAssignment4
            ->method('getCriterionValue')
            ->willReturn($this->getCriterionConfig(['role_id' => 6]));

        return [
            [$criterionAssignment1, $criterionAssignment2, $criterionAssignment3, $criterionAssignment4]
        ];
    }

    /**
     * @dataProvider criteriaAssignmentProvider
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment4
     */
    public function testConstraintAcceptanceWorksAsExpected(
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment4
    ): void {
        $document1 = $this
            ->getMockBuilder(ilTermsOfServiceDocument::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['criteria'])
            ->getMock();

        $document1
            ->method('criteria')
            ->willReturn([$criterionAssignment1, $criterionAssignment2]);

        $constraint = new ilTermsOfServiceDocumentCriterionAssignmentConstraint(
            $this->getTypeMockForConstraint(),
            $document1,
            new Factory(),
            $this->getLanguageMock()
        );

        $this->assertTrue($constraint->accepts($criterionAssignment1));
        $this->assertTrue($constraint->accepts($criterionAssignment2));
        $this->assertFalse($constraint->accepts($criterionAssignment3));
        $this->assertTrue($constraint->accepts($criterionAssignment4));
    }

    /**
     * @dataProvider criteriaAssignmentProvider
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment4
     */
    public function testConstraintCheckWorksAsExpected(
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment4
    ): void {
        $document1 = $this
            ->getMockBuilder(ilTermsOfServiceDocument::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['criteria'])
            ->getMock();

        $document1
            ->method('criteria')
            ->willReturn([$criterionAssignment1, $criterionAssignment2]);

        $constraint = new ilTermsOfServiceDocumentCriterionAssignmentConstraint(
            $this->getTypeMockForConstraint(),
            $document1,
            new Factory(),
            $this->getLanguageMock()
        );

        $raised = false;

        try {
            $constraint->check($criterionAssignment4);
        } catch (UnexpectedValueException $e) {
            $raised = true;
        }

        $this->assertFalse($raised);

        try {
            $constraint->check($criterionAssignment3);
            $raised = false;
        } catch (UnexpectedValueException $e) {
            $this->assertSame('The passed assignment must be unique for the document!', $e->getMessage());
            $raised = true;
        }

        $this->assertTrue($raised);
    }

    /**
     * @dataProvider criteriaAssignmentProvider
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment4
     */
    public function testConstraintProblemDetectionWorksAsExpected(
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment4
    ): void {
        $document1 = $this
            ->getMockBuilder(ilTermsOfServiceDocument::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['criteria'])
            ->getMock();

        $document1
            ->method('criteria')
            ->willReturn([$criterionAssignment1, $criterionAssignment2]);

        $constraint = new ilTermsOfServiceDocumentCriterionAssignmentConstraint(
            $this->getTypeMockForConstraint(),
            $document1,
            new Factory(),
            $this->getLanguageMock()
        );

        $this->assertNull($constraint->problemWith($criterionAssignment1));
        $this->assertNull($constraint->problemWith($criterionAssignment2));
        $this->assertNull($constraint->problemWith($criterionAssignment4));
        $this->assertIsString($constraint->problemWith($criterionAssignment3));
    }

    /**
     * @dataProvider criteriaAssignmentProvider
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment4
     */
    public function testConstraintRestrictionWorksAsExpected(
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment4
    ): void {
        $document1 = $this
            ->getMockBuilder(ilTermsOfServiceDocument::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['criteria'])
            ->getMock();

        $document1
            ->method('criteria')
            ->willReturn([$criterionAssignment1, $criterionAssignment2]);

        $dataFavtgory = new Factory();

        $constraint = new ilTermsOfServiceDocumentCriterionAssignmentConstraint(
            $this->getTypeMockForConstraint(),
            $document1,
            $dataFavtgory,
            $this->getLanguageMock()
        );

        $ok = $dataFavtgory->ok($criterionAssignment1);
        $ok2 = $dataFavtgory->ok($criterionAssignment3);
        $error = $dataFavtgory->error('An error occurred');

        $result = $constraint->applyTo($ok);
        $this->assertTrue($result->isOK());

        $result = $constraint->applyTo($ok2);
        $this->assertTrue($result->isError());

        $result = $constraint->applyTo($error);
        $this->assertSame($error, $result);
    }

    /**
     * @dataProvider criteriaAssignmentProvider
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment4
     */
    public function testConstraintProblemBuilderWorksAsExpected(
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment4
    ): void {
        $document1 = $this
            ->getMockBuilder(ilTermsOfServiceDocument::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['criteria'])
            ->getMock();

        $document1
            ->method('criteria')
            ->willReturn([$criterionAssignment1, $criterionAssignment2]);

        $constraint = new ilTermsOfServiceDocumentCriterionAssignmentConstraint(
            $this->getTypeMockForConstraint(),
            $document1,
            new Factory(),
            $this->getLanguageMock()
        );

        $newConstraint = $constraint->withProblemBuilder(function () {
            return 'phpunit';
        });
        $this->assertSame('phpunit', $newConstraint->problemWith($criterionAssignment3));
    }

    /**
     * @dataProvider criteriaAssignmentProvider
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment4
     */
    public function testConstraintExposesCorrectErrorMessagesAfterMultiAccept(
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment4
    ): void {
        $document1 = $this
            ->getMockBuilder(ilTermsOfServiceDocument::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['criteria'])
            ->getMock();

        $document1
            ->method('criteria')
            ->willReturn([$criterionAssignment1, $criterionAssignment2]);

        $constraint = new ilTermsOfServiceDocumentCriterionAssignmentConstraint(
            $this->getTypeMockForConstraint(),
            $document1,
            new Factory(),
            $this->getLanguageMock()
        );

        $criterionAssignment5 = clone $criterionAssignment3;

        $constraint->accepts($criterionAssignment1);
        $constraint->accepts($criterionAssignment2);
        $constraint->accepts($criterionAssignment3);
        $constraint->accepts($criterionAssignment4);
        $constraint->accepts($criterionAssignment5);

        $this->assertSame(
            'The passed assignment must be unique for the document!',
            $constraint->problemWith($criterionAssignment3)
        );
        $this->assertSame(
            'The passed assignment must be unique for the document!',
            $constraint->problemWith($criterionAssignment5)
        );
    }

    /**
     * @dataProvider criteriaAssignmentProvider
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment4
     */
    public function testCriterionWithSameNatureIsNotAcceptedWhenAlreadyAssigned(
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment4
    ): void {
        $document = $this
            ->getMockBuilder(ilTermsOfServiceDocument::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['criteria'])
            ->getMock();

        $document
            ->method('criteria')
            ->willReturn([$criterionAssignment1, $criterionAssignment2]);

        $criterionTypeFactory = $this->getCriterionTypeFactoryMock();

        $criterionType1 = $this->getCriterionTypeMock('usr_global_role');
        $criterionType2 = $this->getCriterionTypeMock('usr_language');

        $criterionType1
            ->method('hasUniqueNature')
            ->willReturn(false);

        $criterionType2
            ->method('hasUniqueNature')
            ->willReturn(true);

        $criterionTypeFactory
            ->method('findByTypeIdent')
            ->willReturn($criterionType2);

        $constraint = new ilTermsOfServiceDocumentCriterionAssignmentConstraint(
            $criterionTypeFactory,
            $document,
            new Factory(),
            $this->getLanguageMock()
        );

        $criterionWithSameNature = $this
            ->getMockBuilder(ilTermsOfServiceDocumentCriterionAssignment::class)
            ->onlyMethods(['getCriterionValue', 'getCriterionId'])
            ->addMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();

        $criterionWithSameNature
            ->method('getId')
            ->willReturn(0);

        $criterionWithSameNature
            ->method('getCriterionId')
            ->willReturn('usr_language');

        $criterionWithSameNature
            ->method('getCriterionValue')
            ->willReturn($this->getCriterionConfig(['lng' => 'ru']));

        $this->assertFalse($constraint->accepts($criterionWithSameNature));
    }
}
