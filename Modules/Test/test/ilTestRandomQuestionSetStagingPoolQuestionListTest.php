<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestRandomQuestionSetStagingPoolQuestionListTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetStagingPoolQuestionListTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetStagingPoolQuestionList $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionSetStagingPoolQuestionList(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilPluginAdmin::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetStagingPoolQuestionList::class, $this->testObj);
    }

    public function testTestObjId() : void
    {
        $this->testObj->setTestObjId(5);
        $this->assertEquals(5, $this->testObj->getTestObjId());
    }

    public function testTestId() : void
    {
        $this->testObj->setTestId(5);
        $this->assertEquals(5, $this->testObj->getTestId());
    }

    public function testPoolId() : void
    {
        $this->testObj->setPoolId(5);
        $this->assertEquals(5, $this->testObj->getPoolId());
    }

    public function testAddTaxonomyFilter() : void
    {
        $this->testObj->addTaxonomyFilter(20, "test");
        $this->assertEquals([20 => "test"], $this->testObj->getTaxonomyFilters());
    }

    public function testTypeFilter() : void
    {
        $this->testObj->setTypeFilter(5);
        $this->assertEquals(5, $this->testObj->getTypeFilter());
    }

    public function testLifecycleFilter() : void
    {
        $expected = [
            "Hello",
            "World"
        ];

        $this->testObj->setLifecycleFilter($expected);
        $this->assertEquals($expected, $this->testObj->getLifecycleFilter());
    }
}
