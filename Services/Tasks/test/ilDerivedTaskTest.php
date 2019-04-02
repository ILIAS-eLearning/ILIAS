<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once __DIR__ . '/bootstrap.php';

/**
 * @author  <killing@leifos.de>
 */
class ilDerivedTaskTest extends \ilTasksTestBase
{
	public function testTitle()
	{
		$task = new ilDerivedTask("title", 99, 1234, 1000);

		$this->assertEquals('title', $task->getTitle());
	}

	public function testRefId()
	{
		$task = new ilDerivedTask("title", 99, 1234, 1000);

		$this->assertEquals(99, $task->getRefId());
	}

	public function testDeadline()
	{
		$task = new ilDerivedTask("title", 99, 1234, 1000);

		$this->assertEquals(1234, $task->getDeadline());
	}

	public function testStartingTime()
	{
		$task = new ilDerivedTask("title", 99, 1234, 1000);

		$this->assertEquals(1000, $task->getStartingTime());
	}
}