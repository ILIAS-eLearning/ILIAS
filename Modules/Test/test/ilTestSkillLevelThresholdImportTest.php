<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSkillLevelThresholdImportTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSkillLevelThresholdImportTest extends ilTestBaseTestCase
{
    private ilTestSkillLevelThresholdImport $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestSkillLevelThresholdImport();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestSkillLevelThresholdImport::class, $this->testObj);
    }
}