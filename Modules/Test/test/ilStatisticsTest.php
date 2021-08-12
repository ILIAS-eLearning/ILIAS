<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilStatisticsTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilStatisticsTest extends ilTestBaseTestCase
{
    private ilStatistics $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilStatistics();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilStatistics::class, $this->testObj);
    }
}