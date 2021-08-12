<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSkillLevelThresholdImportListTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSkillLevelThresholdImportListTest extends ilTestBaseTestCase
{
    private ilTestSkillLevelThresholdImportList $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestSkillLevelThresholdImportList();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestSkillLevelThresholdImportList::class, $this->testObj);
    }
}