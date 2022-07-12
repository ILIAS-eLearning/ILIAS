<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use PHPUnit\Framework\TestCase;

class ilIndividualAssessmentExporterTest extends TestCase
{
    public function test_objectCreation() : void
    {
        $obj = new ilIndividualAssessmentExporter();
        $this->assertInstanceOf(ilIndividualAssessmentExporter::class, $obj);
    }

    public function test_getXmlExportTailDependencies_no_entity() : void
    {
        $obj = new ilIndividualAssessmentExporter();
        $result = $obj->getXmlExportTailDependencies("no_entity", "", [12,13]);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_getXmlExportTailDependencies_iass() : void
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

    public function test_getValidSchemaVersions() : void
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
