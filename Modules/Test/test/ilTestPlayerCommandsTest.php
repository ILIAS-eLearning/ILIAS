<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestPlayerCommandsTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestPlayerCommandsTest extends ilTestBaseTestCase
{
    private ilTestPlayerCommands $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestPlayerCommands();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestPlayerCommands::class, $this->testObj);
    }
}
