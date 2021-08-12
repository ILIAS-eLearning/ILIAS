<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class assMarkSchemaTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class assMarkSchemaTest extends ilTestBaseTestCase
{
    private ASS_MarkSchema $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ASS_MarkSchema();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ASS_MarkSchema::class, $this->testObj);
    }
}