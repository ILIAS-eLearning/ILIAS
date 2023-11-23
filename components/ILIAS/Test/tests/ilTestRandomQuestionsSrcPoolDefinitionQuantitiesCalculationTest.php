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
 * Class ilTestRandomQuestionsSrcPoolDefinitionQuantitiesCalculationTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionsSrcPoolDefinitionQuantitiesCalculationTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionsSrcPoolDefinitionQuantitiesCalculation $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionsSrcPoolDefinitionQuantitiesCalculation(
            $this->createMock(ilTestRandomQuestionSetSourcePoolDefinition::class),
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestRandomQuestionsSrcPoolDefinitionQuantitiesCalculation::class, $this->testObj);
    }

    public function testSourcePoolDefinition(): void
    {
        $mock = $this->createMock(ilTestRandomQuestionSetSourcePoolDefinition::class);

        $this->testObj->setSourcePoolDefinition($mock);
        $this->assertEquals($mock, $this->testObj->getSourcePoolDefinition());
    }

    public function testIntersectionQuantitySharingDefinitionList(): void
    {
        $mock = $this->createMock(ilTestRandomQuestionSetSourcePoolDefinitionList::class);

        $this->testObj->setIntersectionQuantitySharingDefinitionList($mock);
        $this->assertEquals($mock, $this->testObj->getIntersectionQuantitySharingDefinitionList());
    }

    public function testOverallQuestionAmount(): void
    {
        $overAllQuestionAmount = 5;
        $this->testObj->setOverallQuestionAmount($overAllQuestionAmount);
        $this->assertEquals($overAllQuestionAmount, $this->testObj->getOverallQuestionAmount());
    }

    public function testExclusiveQuestionAmount(): void
    {
        $exclusiveQuestionAmount = 5;
        $this->testObj->setExclusiveQuestionAmount($exclusiveQuestionAmount);
        $this->assertEquals($exclusiveQuestionAmount, $this->testObj->getExclusiveQuestionAmount());
    }

    public function testAvailableSharedQuestionAmount(): void
    {
        $availableSharedQuestionAmount = 5;
        $this->testObj->setAvailableSharedQuestionAmount($availableSharedQuestionAmount);
        $this->assertEquals($availableSharedQuestionAmount, $this->testObj->getAvailableSharedQuestionAmount());
    }
}
