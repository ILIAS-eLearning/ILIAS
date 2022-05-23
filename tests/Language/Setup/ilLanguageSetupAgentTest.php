<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

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

    public function setUp() : void
    {
        $this->refinery = new Refinery($this->createMock(DataFactory::class), $this->createMock(\ilLanguage::class));
        $setup_language = $this->createMock(\ilSetupLanguage::class);

        $this->obj = new \ilLanguageSetupAgent($this->refinery, null, $setup_language);
    }

    public function testCreate() : void
    {
        $this->assertInstanceOf(\ilLanguageSetupAgent::class, $this->obj);
    }

    public function testHasConfig() : void
    {
        $this->assertFalse($this->obj->hasConfig());
    }

    public function testGetArrayToConfigTransformation() : void
    {
        $this->expectException(LogicException::class);
        
        $fnc = $this->obj->getArrayToConfigTransformation();
    }

    public function testGetInstallObjectives() : void
    {
        $objective_collection = $this->obj->getInstallObjective();

        $this->assertEquals('Complete objectives from Services/Language', $objective_collection->getLabel());
        $this->assertFalse($objective_collection->isNotable());
        $this->assertCount(2, $objective_collection->getObjectives());
    }

    public function testGetUpdateObjective() : void
    {
        $objective_collection = $this->obj->getUpdateObjective();

        $this->assertEquals('Complete objectives from Services/Language', $objective_collection->getLabel());
        $this->assertFalse($objective_collection->isNotable());
        $this->assertCount(1, $objective_collection->getObjectives());
    }

    public function testGetBuildArtifactObjective() : void
    {
        $result = $this->obj->getBuildArtifactObjective();

        $this->assertInstanceOf(NullObjective::class, $result);
    }
}
