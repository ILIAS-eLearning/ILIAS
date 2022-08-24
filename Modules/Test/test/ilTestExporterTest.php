<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestExporterTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestExporterTest extends ilTestBaseTestCase
{
    private ilTestExporter $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestExporter();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestExporter::class, $this->testObj);
    }

    public function testGetValidSchemaVersions(): void
    {
        $expected = [
            "4.1.0" => [
                "namespace" => "http://www.ilias.de/Modules/Test/htlm/4_1",
                "xsd_file" => "ilias_tst_4_1.xsd",
                "uses_dataset" => false,
                "min" => "4.1.0",
                "max" => ""
            ]
        ];
        $this->assertEquals($expected, $this->testObj->getValidSchemaVersions("abcd"));
    }
}
