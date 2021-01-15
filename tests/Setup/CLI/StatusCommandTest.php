<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup\CLI;

use ILIAS\Setup;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use ILIAS\Setup\Metrics;
use ILIAS\Setup\Metrics\Metric as M;
use Hamcrest\Core\Set;

class StatusCommandTest extends TestCase
{
    public function testMetrics() : void
    {
        $agent_finder = $this->createMock(Setup\AgentFinder::class);
        $obj = new Setup\CLI\StatusCommand($agent_finder);
        $storage = new Metrics\ArrayStorage();
        $objective = $this->createMock(Setup\Objective::class);
        $agent = $this->createMock(Setup\AgentCollection::class);
        $expected = new M(M::STABILITY_MIXED, M::TYPE_COLLECTION, []);

        $agent
            ->expects($this->once())
            ->method("getStatusObjective")
            ->with($storage)
            ->willReturn(new Setup\ObjectiveCollection("text", false, $objective));

        $result = $obj->getMetrics($agent);

        $this->assertEquals($expected, $result);
    }
}
