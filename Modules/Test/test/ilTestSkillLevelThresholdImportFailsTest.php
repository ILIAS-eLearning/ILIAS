<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSkillLevelThresholdImportFailsTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSkillLevelThresholdImportFailsTest extends ilTestBaseTestCase
{
    private ilTestSkillLevelThresholdImportFails $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestSkillLevelThresholdImportFails(201);
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestSkillLevelThresholdImportFails::class, $this->testObj);
    }
}
