<?php
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

require_once __DIR__ . '/bootstrap.php';

/**
 * @author  <killing@leifos.de>
 */
class ilDerivedTaskFactoryTest extends ilTasksTestBase
{
    public function testConstructor()
    {
        /** @var ilTaskService $service */
        $service = $this->getTaskServiceMock();
        $factory = $service->derived()->factory();

        $this->assertTrue($factory instanceof ilDerivedTaskFactory);
    }

    public function testTask() : void
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
        $this->assertEquals(0, $task->getWspId());
    }

    public function testCollector() : void
    {
        /** @var ilTaskService $service */
        $service = $this->getTaskServiceMock();
        $factory = $service->derived()->factory();

        $task = $factory->collector();

        $this->assertTrue($task instanceof ilDerivedTaskCollector);
    }

    public function testAllProviders() : void
    {
        /** @var ilTaskService $service */
        $service = $this->getTaskServiceMock();
        $factory = $service->derived()->factory();

        $providers = $factory->getAllProviders();
        $this->assertTrue($providers[0] instanceof ilDerivedTaskProvider);
    }
}
