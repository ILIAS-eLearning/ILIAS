<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSkillLevelThresholdImporterTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSkillLevelThresholdImporterTest extends ilTestBaseTestCase
{
    private ilTestSkillLevelThresholdImporter $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestSkillLevelThresholdImporter();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestSkillLevelThresholdImporter::class, $this->testObj);
    }
}