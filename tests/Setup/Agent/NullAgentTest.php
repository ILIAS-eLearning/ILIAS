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
