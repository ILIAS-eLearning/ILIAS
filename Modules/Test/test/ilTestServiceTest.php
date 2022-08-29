<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestServiceTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestServiceTest extends ilTestBaseTestCase
{
    private ilTestService $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestService($this->createMock(ilObjTest::class));
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestService::class, $this->testObj);
    }
}
