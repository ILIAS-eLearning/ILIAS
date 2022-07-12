<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use PHPUnit\Framework\TestCase;

class ilIndividualAssessmentDataSetWrapper extends ilIndividualAssessmentDataSet
{
    public function __construct()
    {
    }

    public function getTypesWrapper(string $entity, string $version) : array
    {
        return $this->getTypes($entity, $version);
    }

    public function getXmlNamespaceWrapper(string $a_entity, string $a_schema_version) : string
    {
        return $this->getXmlNamespace($a_entity, $a_schema_version);
    }
}

class ilIndividualAssessmentDataSetTest extends TestCase
{
    public function test_crateObject() : void
    {
        $obj = new ilIndividualAssessmentDataSetWrapper();
        $this->assertInstanceOf(ilIndividualAssessmentDataSet::class, $obj);
    }

    public function test_getSupportedVersions() : void
    {
        $expected = ['5.2.0', '5.3.0'];

        $obj = new ilIndividualAssessmentDataSetWrapper();
        $result = $obj->getSupportedVersions();

        $this->assertIsArray($result);
        $this->assertEquals($expected, $result);
    }

    public function test_getXmlNamespaceWrapper() : void
    {
        $expected = 'http://www.ilias.de/xml/Modules/IndividualAssessment/entity_string';

        $obj = new ilIndividualAssessmentDataSetWrapper();
        $result = $obj->getXmlNamespaceWrapper("entity_string", "not_implemented");

        $this->assertEquals($expected, $result);
    }

    public function test_getTypes_default() : void
    {
        $obj = new ilIndividualAssessmentDataSetWrapper();
        $result = $obj->getTypesWrapper("no_entity", "");

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_getTypes_iass() : void
    {
        $expected = [
            "id" => "integer",
            "title" => "text",
            "description" => "text",
            "content" => "text",
            "recordTemplate" => "text",
            "eventTimePlaceRequired" => "integer",
            "file_required" => "integer",
            "contact" => "text",
            "responsibility" => "text",
            "phone" => "text",
            "mails" => "text",
            "consultation_hours" => "text"
        ];

        $obj = new ilIndividualAssessmentDataSetWrapper();
        $result = $obj->getTypesWrapper("iass", "");

        $this->assertIsArray($result);
        $this->assertEquals($expected, $result);
    }
}
