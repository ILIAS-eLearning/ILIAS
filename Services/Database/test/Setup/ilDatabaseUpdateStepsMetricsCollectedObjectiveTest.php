<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use PHPUnit\Framework\TestCase;
use ILIAS\Setup\Metrics\Storage;

class Test_ilDatabaseUpdateSteps2 implements ilDatabaseUpdateSteps
{
    protected ?ilDBInterface $db = null;

    public function prepare(ilDBInterface $db)
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

    protected function setUp() : void
    {
        $this->steps = new Test_ilDatabaseUpdateSteps2;
        $this->storage = $this->createMock(Storage::class);
    }

    public function testObjectCreation() : ilDatabaseUpdateStepsMetricsCollectedObjective
    {
        $obj = new ilDatabaseUpdateStepsMetricsCollectedObjective($this->storage, $this->steps);
        $this->assertInstanceOf(ilDatabaseUpdateStepsMetricsCollectedObjective::class, $obj);

        return $obj;
    }
}
