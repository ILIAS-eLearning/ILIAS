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

use PHPUnit\Framework\TestCase;
use ILIAS\Setup\Metrics\Storage;

class Test_ilDatabaseUpdateSteps2 implements ilDatabaseUpdateSteps
{
    protected ?ilDBInterface $db = null;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(ilDBInterface $db)
    {
    }

    public function step_4(ilDBInterface $db)
    {
    }

    public function step_2(ilDBInterface $db)
    {
    }
}

class ilDatabaseUpdateStepsMetricsCollectedObjectiveTest extends TestCase
{
    public Test_ilDatabaseUpdateSteps2 $steps;
    public Storage $storage;

    protected function setUp(): void
    {
        $this->steps = new Test_ilDatabaseUpdateSteps2();
        $this->storage = $this->createMock(Storage::class);
    }

    public function testObjectCreation(): ilDatabaseUpdateStepsMetricsCollectedObjective
    {
        $obj = new ilDatabaseUpdateStepsMetricsCollectedObjective($this->storage, $this->steps);
        $this->assertInstanceOf(ilDatabaseUpdateStepsMetricsCollectedObjective::class, $obj);

        return $obj;
    }
}
