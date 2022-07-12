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
