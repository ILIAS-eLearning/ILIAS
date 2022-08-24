<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestPassFinishTasksTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestPassFinishTasksTest extends ilTestBaseTestCase
{
    private ilTestPassFinishTasks $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $return_statement = $this->getMockBuilder(ilDBStatement::class)->getMock();

        $db_mock = $this->createMock(ilDBInterface::class);
        $db_mock->expects($this->any())
                ->method("queryF")
                ->willReturn($return_statement);

        $this->setGlobalVariable("ilDB", $db_mock);

        $this->testObj = new ilTestPassFinishTasks(0, 0);
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestPassFinishTasks::class, $this->testObj);
    }
}
