<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilStatisticsTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilStatisticsTest extends ilTestBaseTestCase
{
    private ilStatistics $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilStatistics();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilStatistics::class, $this->testObj);
    }

    public function testNANHandling(): void
    {
        $this->assertEquals(NAN_HANDLING_REMOVE, $this->testObj->getNANHandling());

        $this->testObj->setNANHandling(NAN_HANDLING_REMOVE);
        $this->assertEquals(NAN_HANDLING_REMOVE, $this->testObj->getNANHandling());

        $this->testObj->setNANHandling(NAN_HANDLING_ZERO);
        $this->assertEquals(NAN_HANDLING_ZERO, $this->testObj->getNANHandling());
    }

    public function testData(): void
    {
        $input = [
            "1250",
            "125125",
            1518,
            "abasfki",
            -1251
        ];
        $this->testObj->setData($input);

        $expected1 = [
            -1251,
            "1250",
            1518,
            "125125",
        ];
        $this->assertEquals($expected1, $this->testObj->getData());

        $expected2 = [
            -1251,
            0,
            "1250",
            1518,
            "125125",
        ];
        $this->testObj->setNANHandling(NAN_HANDLING_ZERO);
        $this->testObj->setData($input);
        $this->assertEquals($expected2, $this->testObj->getData());
    }
}
