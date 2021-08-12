<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCronFinishUnfinishedTestPassesTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilCronFinishUnfinishedTestPassesTest extends ilTestBaseTestCase
{
    private ilCronFinishUnfinishedTestPasses $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->addGlobal_ilObjDataCache();
        $this->addGlobal_lng();
        $this->addGlobal_ilDB();

        $this->testObj = new ilCronFinishUnfinishedTestPasses();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilCronFinishUnfinishedTestPasses::class, $this->testObj);
    }
}