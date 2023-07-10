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
