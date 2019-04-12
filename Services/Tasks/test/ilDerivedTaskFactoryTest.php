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
		/** @var ilTaskService $service */
		$service = $this->getTaskServiceMock();
		$factory = $service->derived()->factory();

		$this->assertTrue($factory instanceof ilDerivedTaskFactory);
	}

	public function testTask()
	{
		/** @var ilTaskService $service */
		$service = $this->getTaskServiceMock();
		$factory = $service->derived()->factory();

		$task = $factory->task("title", 123, 1234, 1000);

		$this->assertTrue($task instanceof ilDerivedTask);
		$this->assertEquals('title', $task->getTitle());
		$this->assertEquals(123, $task->getRefId());
		$this->assertEquals(1234, $task->getDeadline());
		$this->assertEquals(1000, $task->getStartingTime());
	}

	public function testCollector()
	{
		/** @var ilTaskService $service */
		$service = $this->getTaskServiceMock();
		$factory = $service->derived()->factory();

		$task = $factory->collector();

		$this->assertTrue($task instanceof ilDerivedTaskCollector);
	}

	public function testAllProviders()
	{
		/** @var ilTaskService $service */
		$service = $this->getTaskServiceMock();
		$factory = $service->derived()->factory();

		$providers = $factory->getAllProviders(false, null);
		$this->assertTrue($providers[0] instanceof ilDerivedTaskProvider);
	}

}