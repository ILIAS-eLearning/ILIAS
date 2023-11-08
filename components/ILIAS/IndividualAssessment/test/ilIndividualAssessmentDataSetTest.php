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

class ilIndividualAssessmentDataSetWrapper extends ilIndividualAssessmentDataSet
{
    public function __construct()
    {
    }

    public function getTypesWrapper(string $entity, string $version): array
    {
        return $this->getTypes($entity, $version);
    }

    public function getXmlNamespaceWrapper(string $a_entity, string $a_schema_version): string
    {
        return $this->getXmlNamespace($a_entity, $a_schema_version);
    }
}

class ilIndividualAssessmentDataSetTest extends TestCase
{
    public function test_crateObject(): void
    {
        $obj = new ilIndividualAssessmentDataSetWrapper();
        $this->assertInstanceOf(ilIndividualAssessmentDataSet::class, $obj);
    }

    public function test_getSupportedVersions(): void
    {
        $expected = ['5.2.0', '5.3.0'];

        $obj = new ilIndividualAssessmentDataSetWrapper();
        $result = $obj->getSupportedVersions();

        $this->assertIsArray($result);
        $this->assertEquals($expected, $result);
    }

    public function test_getXmlNamespaceWrapper(): void
    {
        $expected = 'http://www.ilias.de/xml/Modules/IndividualAssessment/entity_string';

        $obj = new ilIndividualAssessmentDataSetWrapper();
        $result = $obj->getXmlNamespaceWrapper("entity_string", "not_implemented");

        $this->assertEquals($expected, $result);
    }

    public function test_getTypes_default(): void
    {
        $obj = new ilIndividualAssessmentDataSetWrapper();
        $result = $obj->getTypesWrapper("no_entity", "");

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_getTypes_iass(): void
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
