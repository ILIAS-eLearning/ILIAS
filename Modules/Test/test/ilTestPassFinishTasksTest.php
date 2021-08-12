<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestPassFinishTasksTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestPassFinishTasksTest extends ilTestBaseTestCase
{
    private ilTestPassFinishTasks $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $class = new class {
            public function numRows()
            {
                return 0;
            }
        };
        $returnObj = new $class();

        $db_mock = $this->createMock(ilDBInterface::class);
        $db_mock->expects($this->any())
                ->method("queryF")
                ->willReturn($returnObj);

        $this->setGlobalVariable("ilDB", $db_mock);

        $this->testObj = new ilTestPassFinishTasks(0, 0);
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestPassFinishTasks::class, $this->testObj);
    }
}