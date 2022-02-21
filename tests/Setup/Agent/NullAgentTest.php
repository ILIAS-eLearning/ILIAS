<?php declare(strict_types=1);

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup\Agent;

use ILIAS\Setup\Objective\NullObjective;
use ILIAS\Setup\Agent\NullAgent;
use ILIAS\Setup\Metrics;
use PHPUnit\Framework\TestCase;

class NullAgentTest extends TestCase
{
    public function setUp() : void
    {
        $this->refinery = $this->createMock(\ILIAS\Refinery\Factory::class);
        $this->storage = $this->createMock(Metrics\Storage::class);
        $this->refinery = $this->createMock(\ILIAS\Refinery\Factory::class);
        $this->agent = new NullAgent($this->refinery);
    }

    public function testIsNull() : void
    {
        $null = new NullObjective();
        $this->assertFalse($this->agent->hasConfig());
        $this->assertEquals($null, $this->agent->getInstallObjective());
        $this->assertEquals($null, $this->agent->getUpdateObjective());
        $this->assertEquals($null, $this->agent->getBuildArtifactObjective());
        $this->assertEquals($null, $this->agent->getStatusObjective($this->storage));
        $this->assertEquals([], $this->agent->getMigrations());
    }

    public function testGetArrayToConfigTransformationThrows() : void
    {
        $this->expectException(\LogicException::class);
        $this->agent->getArrayToConfigTransformation();
    }
}
