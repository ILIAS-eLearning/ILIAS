<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilRandomTestDataTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilRandomTestDataTest extends ilTestBaseTestCase
{
    private ilRandomTestData $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilRandomTestData("150", "testString");
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilRandomTestData::class, $this->testObj);
    }

    public function test__get(): void
    {
        $testObj = new ilRandomTestData();
        $this->assertEquals(0, $testObj->__get("count"));
        $this->assertEquals("", $testObj->__get("qpl"));

        $this->assertEquals(150, $this->testObj->__get("count"));
        $this->assertEquals("testString", $this->testObj->__get("qpl"));

        $this->assertNull($testObj->__get("abcd"));
    }

    public function test__set(): void
    {
        $this->testObj->__set("count", 1125);
        $this->assertEquals(1125, $this->testObj->__get("count"));

        $this->testObj->__set("qpl", "ttt");
        $this->assertEquals("ttt", $this->testObj->__get("qpl"));
    }
}
