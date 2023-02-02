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

use PHPUnit\Framework\TestCase;

class ilIndividualAssessmentExporterTest extends TestCase
{
    public function test_objectCreation(): void
    {
        $obj = new ilIndividualAssessmentExporter();
        $this->assertInstanceOf(ilIndividualAssessmentExporter::class, $obj);
    }

    public function test_getXmlExportTailDependencies_no_entity(): void
    {
        $obj = new ilIndividualAssessmentExporter();
        $result = $obj->getXmlExportTailDependencies("no_entity", "", [12,13]);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_getXmlExportTailDependencies_iass(): void
    {
        $expected[] = [
            "component" => "Services/Object",
            "entity" => "common",
            "ids" => [12,13]
        ];

        $obj = new ilIndividualAssessmentExporter();
        $result = $obj->getXmlExportTailDependencies("iass", "", [12,13]);

        $this->assertIsArray($result);
        $this->assertEquals($expected, $result);
    }

    public function test_getValidSchemaVersions(): void
    {
        $expected = [
            "5.2.0" => [
                "namespace" => "http://www.ilias.de/Services/User/iass/5_2",
                "xsd_file" => "ilias_iass_5_2.xsd",
                "uses_dataset" => true,
                "min" => "5.2.0",
                "max" => "5.2.99"
            ],
            "5.3.0" => [
                "namespace" => "http://www.ilias.de/Services/User/iass/5_3",
                "xsd_file" => "ilias_iass_5_3.xsd",
                "uses_dataset" => true,
                "min" => "5.3.0",
                "max" => ""
            ]
        ];

        $obj = new ilIndividualAssessmentExporter();
        $result = $obj->getValidSchemaVersions("");

        $this->assertIsArray($result);
        $this->assertEquals($expected, $result);
    }
}
