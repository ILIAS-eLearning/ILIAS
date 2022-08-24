<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestEvaluationDataTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestEvaluationDataTest extends ilTestBaseTestCase
{
    private ilTestEvaluationData $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestEvaluationData();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestEvaluationData::class, $this->testObj);
    }

    public function test__sleep(): void
    {
        $expected = ['questionTitles', 'participants', 'statistics', 'arrFilter', 'datasets', 'test'];

        $this->assertEquals($expected, $this->testObj->__sleep());
    }

    public function testAccessFilteredParticipantList(): void
    {
        $value_mock = $this->createMock(ilTestParticipantList::class);
        $this->testObj->setAccessFilteredParticipantList($value_mock);

        $this->assertEquals($value_mock, $this->testObj->getAccessFilteredParticipantList());
    }

    public function testTest(): void
    {
        $value_mock = $this->createMock(ilObjTest::class);
        $this->testObj->setTest($value_mock);

        $this->assertEquals($value_mock, $this->testObj->getTest());
    }

    public function testDatasets(): void
    {
        $this->testObj->setDatasets(20);

        $this->assertEquals(20, $this->testObj->getDatasets());
    }

    public function testQuestionTitle(): void
    {
        $expected = [
            120 => "abcd",
            121 => "Hello",
            2150 => "World",
        ];

        foreach ($expected as $questionId => $questionTitle) {
            $this->testObj->addQuestionTitle($questionId, $questionTitle);
        }

        $this->assertEquals($expected, $this->testObj->getQuestionTitles());

        $this->assertEquals($expected[2150], $this->testObj->getQuestionTitle(2150));
    }

    public function testCalculateStatistics(): void
    {
        $this->assertNull($this->testObj->statistics);

        $this->testObj->calculateStatistics();

        $this->assertInstanceOf(ilTestStatistics::class, $this->testObj->statistics);
    }

    public function testFilter(): void
    {
        $this->assertNull($this->testObj->arrFilter);

        $this->testObj->resetFilter();
        $this->assertEquals([], $this->testObj->arrFilter);

        $this->testObj->setFilter("abd", "hello");
        $this->assertEquals(["abd" => "hello"], $this->testObj->arrFilter);

        $this->testObj->setFilterArray(["abd" => "hello"]);
        $this->assertEquals(["abd" => "hello"], $this->testObj->arrFilter);
    }

    public function testGetStatistics(): void
    {
        $this->assertNull($this->testObj->statistics);

        $this->testObj->calculateStatistics();

        $this->assertInstanceOf(ilTestStatistics::class, $this->testObj->getStatistics());
    }
}
