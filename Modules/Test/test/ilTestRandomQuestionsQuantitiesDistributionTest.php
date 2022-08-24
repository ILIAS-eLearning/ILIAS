<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestRandomQuestionsQuantitiesDistributionTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionsQuantitiesDistributionTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionsQuantitiesDistribution $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionsQuantitiesDistribution(
            $this->createMock(
                ilTestRandomSourcePoolDefinitionQuestionCollectionProvider::class
            )
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestRandomQuestionsQuantitiesDistribution::class, $this->testObj);
    }

    public function testQuestionCollectionProvider(): void
    {
        $mock = $this->createMock(ilTestRandomSourcePoolDefinitionQuestionCollectionProvider::class);

        $this->testObj->setQuestionCollectionProvider($mock);
        $this->assertEquals($mock, $this->testObj->getQuestionCollectionProvider());
    }

    public function testSourcePoolDefinitionList(): void
    {
        $mock = $this->createMock(ilTestRandomQuestionSetSourcePoolDefinitionList::class);

        $this->testObj->setSourcePoolDefinitionList($mock);
        $this->assertEquals($mock, $this->testObj->getSourcePoolDefinitionList());
    }
}
