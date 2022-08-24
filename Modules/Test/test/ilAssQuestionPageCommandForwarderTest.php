<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAssQuestionPageCommandForwarderTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilAssQuestionPageCommandForwarderTest extends ilTestBaseTestCase
{
    private ilAssQuestionPageCommandForwarder $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilAssQuestionPageCommandForwarder();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilAssQuestionPageCommandForwarder::class, $this->testObj);
    }

    public function testTestObj(): void
    {
        $testObj = $this->createMock(ilObjTest::class);
        $this->testObj->setTestObj($testObj);

        $this->assertEquals($testObj, $this->testObj->getTestObj());
    }
}
