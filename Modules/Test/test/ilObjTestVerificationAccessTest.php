<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjTestVerificationAccessTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilObjTestVerificationAccessTest extends ilTestBaseTestCase
{
    private ilObjTestVerificationAccess $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilObjTestVerificationAccess();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilObjTestVerificationAccess::class, $this->testObj);
    }
}
