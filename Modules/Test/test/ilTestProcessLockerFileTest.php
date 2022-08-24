<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestProcessLockerFileTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestProcessLockerFileTest extends ilTestBaseTestCase
{
    private ilTestProcessLockerFile $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestProcessLockerFile(
            $this->createMock(ilTestProcessLockFileStorage::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestProcessLockerFile::class, $this->testObj);
    }
}
