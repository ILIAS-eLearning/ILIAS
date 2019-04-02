<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once __DIR__ . '/bootstrap.php';

/**
 * @author  <killing@leifos.de>
 */
class ilDerivedTaskFactoryTest extends \ilTasksTestBase
{
	public function testConstructor()
	{
		$service = $this->getTaskServiceMock();
		$factory = new ilDerivedTaskFactory($service);

		$this->assertTrue($factory instanceof ilDerivedTaskFactory);
	}

	public function testTask()
	{
		$service = $this->getTaskServiceMock();
		$factory = new ilDerivedTaskFactory($service);

		$task = $factory->task("title", 123, 1234, 1000);

		$this->assertTrue($task instanceof ilDerivedTask);
		$this->assertEquals('title', $task->getTitle());
		$this->assertEquals(123, $task->getRefId());
		$this->assertEquals(1234, $task->getDeadline());
		$this->assertEquals(1000, $task->getStartingTime());
	}

	public function testCollector()
	{
		$service = $this->getTaskServiceMock();
		$factory = new ilDerivedTaskFactory($service);

		$task = $factory->collector();

		$this->assertTrue($task instanceof ilDerivedTaskCollector);
	}

	public function testAllProviders()
	{
		$service = $this->getTaskServiceMock();
		$factory = new ilDerivedTaskFactory($service);

		$providers = $factory->getAllProviders(false, null);
		$this->assertTrue($providers[0] instanceof ilDerivedTaskProvider);
	}

}