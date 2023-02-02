<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
