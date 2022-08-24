<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjTestVerificationTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilObjTestVerificationTest extends ilTestBaseTestCase
{
    private ilObjTestVerification $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilDB();
        $this->addGlobal_ilias();
        $this->addGlobal_ilLog();
        $this->addGlobal_ilErr();
        $this->addGlobal_tree();
        $this->addGlobal_ilAppEventHandler();
        $this->addGlobal_objDefinition();

        $this->testObj = new ilObjTestVerification();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilObjTestVerification::class, $this->testObj);
    }
}
