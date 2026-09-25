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

declare(strict_types=1);

namespace ILIAS\Tests\KeyValueStorage\Setup;

use ILIAS\KeyValueStorage\Setup\Agent;
use ILIAS\KeyValueStorage\Setup\DBUpdateSteps;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Setup\Metrics\Storage;
use PHPUnit\Framework\TestCase;

class AgentTest extends TestCase
{
    private Agent $agent;

    protected function setUp(): void
    {
        $this->agent = new Agent($this->createStub(Refinery::class));
    }

    public function testUpdateRunsTheDatabaseSteps(): void
    {
        $objective = $this->agent->getUpdateObjective();

        $this->assertInstanceOf(\ilDatabaseUpdateStepsExecutedObjective::class, $objective);
        $this->assertSame(
            (new \ilDatabaseUpdateStepsExecutedObjective(new DBUpdateSteps()))->getHash(),
            $objective->getHash()
        );
    }

    public function testStatusCollectsTheDatabaseStepMetrics(): void
    {
        $objective = $this->agent->getStatusObjective($this->createStub(Storage::class));

        $this->assertInstanceOf(\ilDatabaseUpdateStepsMetricsCollectedObjective::class, $objective);
    }

    public function testTheAgentHasNothingToConfigure(): void
    {
        $this->assertFalse($this->agent->hasConfig());
    }
}
