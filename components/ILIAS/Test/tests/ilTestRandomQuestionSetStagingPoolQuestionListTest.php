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
 * Class ilTestRandomQuestionSetStagingPoolQuestionListTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetStagingPoolQuestionListTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetStagingPoolQuestionList $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionSetStagingPoolQuestionList(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilComponentRepository::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetStagingPoolQuestionList::class, $this->testObj);
    }

    public function testTestObjId(): void
    {
        $testObjId = 5;
        $this->testObj->setTestObjId($testObjId);
        $this->assertEquals($testObjId, $this->testObj->getTestObjId());
    }

    public function testTestId(): void
    {
        $testId = 5;
        $this->testObj->setTestId($testId);
        $this->assertEquals($testId, $this->testObj->getTestId());
    }

    public function testPoolId(): void
    {
        $poolId = 5;
        $this->testObj->setPoolId($poolId);
        $this->assertEquals($poolId, $this->testObj->getPoolId());
    }

    public function testAddTaxonomyFilter(): void
    {
        $taxId = 20;
        $taxNodes = 'test';
        $this->testObj->addTaxonomyFilter($taxId, $taxNodes);
        $this->assertEquals([$taxId => $taxNodes], $this->testObj->getTaxonomyFilters());
    }

    public function testTypeFilter(): void
    {
        $typeFilter = 5;
        $this->testObj->setTypeFilter($typeFilter);
        $this->assertEquals($typeFilter, $this->testObj->getTypeFilter());
    }

    public function testLifecycleFilter(): void
    {
        $expected = [
            'Hello',
            'World'
        ];

        $this->testObj->setLifecycleFilter($expected);
        $this->assertEquals($expected, $this->testObj->getLifecycleFilter());
    }
}
