<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once __DIR__ . '/bootstrap.php';

/**
 * @author  <killing@leifos.de>
 */
class ilDerivedTaskCollectorTest extends \ilTasksTestBase
{
	public function testGetEntries()
	{
		$service = $this->getTaskServiceMock();
		$factory = new ilDerivedTaskFactory($service);

		$collector = $factory->collector();

		$entries = $collector->getEntries(0);

		$this->assertTrue($entries[0] instanceof ilDerivedTask);
		$this->assertEquals("title", $entries[0]->getTitle());
	}
}