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

namespace ILIAS\Tests\Setup;

use ILIAS\Setup\Agent;
use ILIAS\Setup\NamedAgent;
use ILIAS\Setup\ImplementationOfAgentFinder;
use ILIAS\Setup\ImplementationOfInterfaceFinder;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Language\Language;
use PHPUnit\Framework\TestCase;

class ImplementationOfAgentFinderTest extends TestCase
{
    private function finder(array $component_agents): ImplementationOfAgentFinder
    {
        $refinery = new Refinery(new DataFactory(), $this->createStub(Language::class));

        return new ImplementationOfAgentFinder(
            $refinery,
            new DataFactory(),
            $this->createStub(Language::class),
            $this->createStub(ImplementationOfInterfaceFinder::class),
            $component_agents
        );
    }

    public function testNamedAgentIsKeyedByItsDeclaredName(): void
    {
        $named = $this->createStub(NamedAgent::class);
        $named->method('getAgentName')->willReturn('content_isolation');

        $collection = $this->finder([$named])->getComponentAgents();

        $this->assertSame($named, $collection->getAgent('content_isolation'));
    }

    public function testPlainAgentFallsBackToClassNameKeying(): void
    {
        $plain = $this->createStub(Agent::class);

        $collection = $this->finder([$plain])->getComponentAgents();

        // no semantic name -> not reachable under one, but reachable by class name
        $this->assertNull($collection->getAgent('content_isolation'));
        $this->assertSame($plain, $collection->getAgent($plain::class));
    }
}
