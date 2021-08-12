<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestRandomQuestionsQuantitiesDistributionTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionsQuantitiesDistributionTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionsQuantitiesDistribution $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionsQuantitiesDistribution(
            $this->createMock(
                ilTestRandomSourcePoolDefinitionQuestionCollectionProvider::class
            )
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestRandomQuestionsQuantitiesDistribution::class, $this->testObj);
    }
}