<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilRandomTestDataTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilRandomTestDataTest extends ilTestBaseTestCase
{
    private ilRandomTestData $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilRandomTestData();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilRandomTestData::class, $this->testObj);
    }
}