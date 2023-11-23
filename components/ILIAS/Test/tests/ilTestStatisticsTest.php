<?php

class ilTestStatisticsTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $ilTestStatistics = new ilTestStatistics($this->createMock(ilTestEvaluationData::class));
        $this->assertInstanceOf(ilTestStatistics::class, $ilTestStatistics);
        $this->assertInstanceOf(ilStatistics::class, $ilTestStatistics->statistics);
    }

    public function testGetStatistics(): void
    {
        $ilTestStatistics = new ilTestStatistics($this->createMock(ilTestEvaluationData::class));
        $this->assertInstanceOf(ilTestStatistics::class, $ilTestStatistics);
        $this->assertInstanceOf(ilStatistics::class, $ilTestStatistics->getStatistics());
        $object = (object) [];
        $ilTestStatistics->statistics = $object;
        $this->assertEquals($object, $ilTestStatistics->getStatistics());
    }

    public function testCalculateStatistics(): void
    {
        $this->markTestSkipped();
    }
}