<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestRandomQuestionsSrcPoolDefinitionQuantitiesCalculationTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionsSrcPoolDefinitionQuantitiesCalculationTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionsSrcPoolDefinitionQuantitiesCalculation $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionsSrcPoolDefinitionQuantitiesCalculation(
            $this->createMock(ilTestRandomQuestionSetSourcePoolDefinition::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestRandomQuestionsSrcPoolDefinitionQuantitiesCalculation::class, $this->testObj);
    }
}