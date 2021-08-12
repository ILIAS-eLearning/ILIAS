<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class assMarkTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class assMarkTest extends ilTestBaseTestCase
{
    private ASS_Mark $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ASS_Mark();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ASS_Mark::class, $this->testObj);
    }
}