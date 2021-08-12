<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestFixedQuestionSetConfigTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestFixedQuestionSetConfigTest extends ilTestBaseTestCase
{
    private ilTestFixedQuestionSetConfig $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestFixedQuestionSetConfig(
            $this->createMock(ilTree::class),
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilPluginAdmin::class),
            $this->createMock(ilObjTest::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestFixedQuestionSetConfig::class, $this->testObj);
    }
}