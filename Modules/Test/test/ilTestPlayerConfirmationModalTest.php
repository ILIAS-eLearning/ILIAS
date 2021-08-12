<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestPlayerConfirmationModalTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestPlayerConfirmationModalTest extends ilTestBaseTestCase
{
    private ilTestPlayerConfirmationModal $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestPlayerConfirmationModal();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestPlayerConfirmationModal::class, $this->testObj);
    }
}