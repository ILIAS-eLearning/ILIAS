<?php declare(strict_types=1);

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
