<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSessionTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSessionTest extends ilTestBaseTestCase
{
    private ilTestSession $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestSession();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestSession::class, $this->testObj);
    }
}