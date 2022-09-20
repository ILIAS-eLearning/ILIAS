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
class ilDerivedTaskCollectorTest extends ilTasksTestBase
{
    public function testGetEntries(): void
    {
        /** @var ilTaskService $service */
        $service = $this->getTaskServiceMock();
        $factory = $service->derived()->factory();

        $collector = $factory->collector();

        $entries = $collector->getEntries(0);

        $this->assertTrue($entries[0] instanceof ilDerivedTask);
        $this->assertEquals("title", $entries[0]->getTitle());
    }
}
