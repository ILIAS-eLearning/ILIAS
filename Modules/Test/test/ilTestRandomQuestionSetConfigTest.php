<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestRandomQuestionSetConfigTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetConfigTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetConfig $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionSetConfig(
            $this->createMock(ilTree::class),
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilPluginAdmin::class),
            $this->createMock(ilObjTest::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetConfig::class, $this->testObj);
    }
}