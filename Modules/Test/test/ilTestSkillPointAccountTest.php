<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSkillPointAccountTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSkillPointAccountTest extends ilTestBaseTestCase
{
    private ilTestSkillPointAccount $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestSkillPointAccount();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestSkillPointAccount::class, $this->testObj);
    }
}