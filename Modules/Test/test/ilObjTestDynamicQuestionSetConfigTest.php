<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjTestDynamicQuestionSetConfigTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilObjTestDynamicQuestionSetConfigTest extends ilTestBaseTestCase
{
    private ilObjTestDynamicQuestionSetConfig $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilObjTestDynamicQuestionSetConfig(
            $this->createMock(ilTree::class),
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilPluginAdmin::class),
            $this->createMock(ilObjTest::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilObjTestDynamicQuestionSetConfig::class, $this->testObj);
    }
}