<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestPasswordCheckerTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestPasswordCheckerTest extends ilTestBaseTestCase
{
    private ilTestPasswordChecker $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestPasswordChecker(
            $this->createMock(ilRbacSystem::class),
            $this->createMock(ilObjUser::class),
            $this->createMock(ilObjTest::class),
            $this->createMock(ilLanguage::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestPasswordChecker::class, $this->testObj);
    }
}
