<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestRandomQuestionSetStagingPoolQuestionListTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetStagingPoolQuestionListTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetStagingPoolQuestionList $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionSetStagingPoolQuestionList(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilPluginAdmin::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetStagingPoolQuestionList::class, $this->testObj);
    }
}