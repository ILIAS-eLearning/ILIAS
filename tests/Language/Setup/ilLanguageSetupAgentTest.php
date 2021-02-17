<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Language\Setup;

use PHPUnit\Framework\TestCase;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Setup\Objective\NullObjective;

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
        $this->assertTrue($this->obj->hasConfig());
    }

    public function testGetArrayToConfigTransformationWithDefaultLanguage() : void
    {
        $fnc = $this->obj->getArrayToConfigTransformation();

        $lng_setup_conf = $fnc([]);

        $this->assertEquals('en', $lng_setup_conf->getDefaultLanguage());
        $this->assertEquals(['en'], $lng_setup_conf->getInstallLanguages());
        $this->assertEquals([], $lng_setup_conf->getInstallLocalLanguages());
    }

    public function testGetArrayToConfigTransformationWithDELanguage() : void
    {
        $fnc = $this->obj->getArrayToConfigTransformation();

        $lng_setup_conf = $fnc([
            'install_languages' => ['en', 'de'],
            'install_local_languages' => ['de']
        ]);

        $this->assertEquals('en', $lng_setup_conf->getDefaultLanguage());
        $this->assertEquals(['en', 'de'], $lng_setup_conf->getInstallLanguages());
        $this->assertEquals(['de'], $lng_setup_conf->getInstallLocalLanguages());
    }

    public function testGetInstallObjectives() : void
    {
        $setup_conf_mock = $this->createMock(\ilLanguageSetupConfig::class);
        $objective_collection = $this->obj->getInstallObjective($setup_conf_mock);

        $this->assertEquals('Complete objectives from Services/Language', $objective_collection->getLabel());
        $this->assertFalse($objective_collection->isNotable());
        $this->assertEquals(3, count($objective_collection->getObjectives()));
    }

    public function testGetUpdateObjectiveWithConfig() : void
    {
        $setup_conf_mock = $this->createMock(\ilLanguageSetupConfig::class);
        $objective_collection = $this->obj->getUpdateObjective($setup_conf_mock);

        $this->assertEquals('Complete objectives from Services/Language', $objective_collection->getLabel());
        $this->assertFalse($objective_collection->isNotable());
        $this->assertEquals(3, count($objective_collection->getObjectives()));
    }

    public function testGetUpdateObjectiveWithoutConfig() : void
    {
        $objective_collection = $this->obj->getUpdateObjective();

        $this->assertEquals('Complete objectives from Services/Language', $objective_collection->getLabel());
        $this->assertFalse($objective_collection->isNotable());
        $this->assertEquals(1, count($objective_collection->getObjectives()));
    }


    public function testGetBuildArtifactObjective() : void
    {
        $result = $this->obj->getBuildArtifactObjective();

        $this->assertInstanceOf(NullObjective::class, $result);
    }
}
