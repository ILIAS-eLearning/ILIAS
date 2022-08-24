<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestProcessLockerNoneTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestProcessLockerNoneTest extends ilTestBaseTestCase
{
    private ilTestProcessLockerNone $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestProcessLockerNone();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestProcessLockerNone::class, $this->testObj);
    }
}
