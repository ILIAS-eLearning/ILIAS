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
