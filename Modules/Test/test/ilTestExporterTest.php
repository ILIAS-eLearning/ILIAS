<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestExporterTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestExporterTest extends ilTestBaseTestCase
{
    private ilTestExporter $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestExporter();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestExporter::class, $this->testObj);
    }
}