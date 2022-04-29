<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once __DIR__ . '/bootstrap.php';

/**
 * @author  <killing@leifos.de>
 */
class ilDerivedTaskTest extends ilTasksTestBase
{
    public function testTitle() : void
    {
        $task = new ilDerivedTask("title", 99, 1234, 1000, 0);

        $this->assertEquals('title', $task->getTitle());
    }

    public function testRefId() : void
    {
        $task = new ilDerivedTask("title", 99, 1234, 1000, 0);

        $this->assertEquals(99, $task->getRefId());
    }

    public function testDeadline() : void
    {
        $task = new ilDerivedTask("title", 99, 1234, 1000, 0);

        $this->assertEquals(1234, $task->getDeadline());
    }

    public function testStartingTime() : void
    {
        $task = new ilDerivedTask("title", 99, 1234, 1000, 0);

        $this->assertEquals(1000, $task->getStartingTime());
    }

    public function testWspId() : void
    {
        $task = new ilDerivedTask("title", 99, 1234, 1000, 0);

        $this->assertEquals(0, $task->getWspId());
    }
}
