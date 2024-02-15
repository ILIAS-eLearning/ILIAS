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

namespace ILIAS\LegalDocuments\test\Setup;

use ILIAS\LegalDocuments\Setup\ConsumerObjective;
use ilDatabaseUpdateStepsExecutedObjective;
use ILIAS\Setup\Metrics\Storage;
use ILIAS\Setup\Objective\NullObjective;
use ILIAS\Refinery\Transformation;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\Refinery\Factory as Refinery;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\Setup\Agent;

require_once __DIR__ . '/../ContainerMock.php';

class AgentTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(Agent::class, new Agent($this->mock(Refinery::class)));
    }

    public function testHasConfig(): void
    {
        $this->assertFalse((new Agent($this->mock(Refinery::class)))->hasConfig());
    }

    public function testGetArrayToConfigTransformation(): void
    {
        $identity = $this->mock(Transformation::class);
        $this->assertSame($identity, (new Agent($this->mockTree(Refinery::class, ['identity' => $identity])))->getArrayToConfigTransformation());
    }

    public function testGetInstallObjective(): void
    {
        $this->assertInstanceOf(NullObjective::class, (new Agent($this->mock(Refinery::class)))->getInstallObjective());
    }

    public function testGetUpdateObjective(): void
    {
        $this->assertInstanceOf(ilDatabaseUpdateStepsExecutedObjective::class, (new Agent($this->mock(Refinery::class)))->getUpdateObjective());
    }

    public function testGetBuildArtifactObjective(): void
    {
        $this->assertInstanceOf(ConsumerObjective::class, (new Agent($this->mock(Refinery::class)))->getBuildObjective());
    }

    public function testGetStatusObjective(): void
    {
        $this->assertInstanceOf(NullObjective::class, (new Agent($this->mock(Refinery::class)))->getStatusObjective($this->mock(Storage::class)));
    }

    public function testGetMigrations(): void
    {
        $this->assertSame([], (new Agent($this->mock(Refinery::class)))->getMigrations());
    }

    public function testGetNamedObjectives(): void
    {
        $this->assertSame([], (new Agent($this->mock(Refinery::class)))->getNamedObjectives());
    }
}
