<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestProcessLockerDbTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestProcessLockerDbTest extends ilTestBaseTestCase
{
    private ilTestProcessLockerDb $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestProcessLockerDb(
            $this->createMock(ilDBInterface::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestProcessLockerDb::class, $this->testObj);
    }
}
