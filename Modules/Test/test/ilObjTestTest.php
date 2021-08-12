<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjTestTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilObjTestTest extends ilTestBaseTestCase
{
    private ilObjTest $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->addGlobal_ilUser();
        $this->addGlobal_lng();
        $this->addGlobal_ilias();
        $this->addGlobal_ilDB();
        $this->addGlobal_ilLog();
        $this->addGlobal_ilErr();
        $this->addGlobal_tree();
        $this->addGlobal_ilAppEventHandler();
        $this->addGlobal_objDefinition();

        define("DEBUG", 0);

        $this->testObj = new ilObjTest();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilObjTest::class, $this->testObj);
    }
}