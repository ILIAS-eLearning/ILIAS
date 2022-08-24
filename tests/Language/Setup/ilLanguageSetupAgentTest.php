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
 ********************************************************************
 */

namespace ILIAS\Tests\Language\Setup;

use PHPUnit\Framework\TestCase;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Setup\Objective\NullObjective;
use LogicException;

class ilLanguageSetupAgentTest extends TestCase
{
    /**
     * @var \ilLanguageSetupAgent
     */
    protected $obj;

    public function setUp(): void
    {
        $this->refinery = new Refinery($this->createMock(DataFactory::class), $this->createMock(\ilLanguage::class));
        $setup_language = $this->createMock(\ilSetupLanguage::class);

        $this->obj = new \ilLanguageSetupAgent($this->refinery, null, $setup_language);
    }

    public function testCreate(): void
    {
        $this->assertInstanceOf(\ilLanguageSetupAgent::class, $this->obj);
    }

    public function testHasConfig(): void
    {
        $this->assertFalse($this->obj->hasConfig());
    }

    public function testGetArrayToConfigTransformation(): void
    {
        $this->expectException(LogicException::class);

        $fnc = $this->obj->getArrayToConfigTransformation();
    }

    public function testGetInstallObjectives(): void
    {
        $objective_collection = $this->obj->getInstallObjective();

        $this->assertEquals('Complete objectives from Services/Language', $objective_collection->getLabel());
        $this->assertFalse($objective_collection->isNotable());
        $this->assertCount(2, $objective_collection->getObjectives());
    }

    public function testGetUpdateObjective(): void
    {
        $objective_collection = $this->obj->getUpdateObjective();

        $this->assertEquals('Complete objectives from Services/Language', $objective_collection->getLabel());
        $this->assertFalse($objective_collection->isNotable());
        $this->assertCount(1, $objective_collection->getObjectives());
    }

    public function testGetBuildArtifactObjective(): void
    {
        $result = $this->obj->getBuildArtifactObjective();

        $this->assertInstanceOf(NullObjective::class, $result);
    }
}
