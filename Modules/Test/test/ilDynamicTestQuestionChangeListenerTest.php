<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDynamicTestQuestionChangeListenerTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilDynamicTestQuestionChangeListenerTest extends ilTestBaseTestCase
{
    private ilDynamicTestQuestionChangeListener $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilDynamicTestQuestionChangeListener($this->createMock(ilDBInterface::class));
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilDynamicTestQuestionChangeListener::class, $this->testObj);
    }

    public function testTestObj() : void
    {
        $expected = [20, 1250, 1250];
        $testObj = $this->createMock(ilObjTest::class);
        foreach ($expected as $value) {
            $this->testObj->addTestObjId($value);
        }

        $this->assertEquals($expected, $this->testObj->getTestObjIds());
    }
}