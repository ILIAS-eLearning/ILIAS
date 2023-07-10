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
