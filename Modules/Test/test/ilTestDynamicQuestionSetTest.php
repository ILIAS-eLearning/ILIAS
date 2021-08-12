<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestDynamicQuestionSetTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestDynamicQuestionSetTest extends ilTestBaseTestCase
{
    private ilTestDynamicQuestionSet $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestDynamicQuestionSet(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilLanguage::class),
            $this->createMock(ilPluginAdmin::class),
            $this->createMock(ilObjTest::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestDynamicQuestionSet::class, $this->testObj);
    }
}