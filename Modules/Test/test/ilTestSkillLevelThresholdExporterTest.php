<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSkillLevelThresholdExporterTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSkillLevelThresholdExporterTest extends ilTestBaseTestCase
{
    private ilTestSkillLevelThresholdExporter $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestSkillLevelThresholdExporter();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestSkillLevelThresholdExporter::class, $this->testObj);
    }
}