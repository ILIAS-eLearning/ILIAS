<?php

/* Copyright (c) 2021 Richard Klees <richard.klees@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

class ilComponentDefinitionInfoProcessorTest extends TestCase
{
    protected ilComponentDefinitionInfoProcessor $processor1;

    protected function setUp() : void
    {
        $this->processor = new ilComponentInfoDefinitionProcessor();
    }

    public function testPurge() : void
    {
        $type = "Modules";
        $name = "NAME";
        $id = "ID";

        $this->processor->beginComponent($name, $type);
        $this->processor->beginTag("module", ["id" => $id]);
        $this->processor->purge();

        $this->assertEquals([ \ilArtifactComponentDataDB::BY_TYPE_AND_NAME => [], \ilArtifactComponentDataDB::BY_ID => []], $this->processor->getData());
    }

    public function testBeginTag() : void
    {
        $type1 = "Modules";
        $name1 = "NAME1";
        $id1 = "ID1";
        $name2 = "NAME2";
        $id2 = "ID2";

        $type2 = "Services";
        $name3 = "NAME3";
        $id3 = "ID3";
        $name4 = "NAME4";
        $id4 = "ID4";

        $this->processor->beginComponent($name1, $type1);
        $this->processor->beginTag("module", ["id" => $id1]);

        $this->processor->beginComponent($name2, $type1);
        $this->processor->beginTag("module", ["id" => $id2]);

        $this->processor->beginComponent($name3, $type2);
        $this->processor->beginTag("service", ["id" => $id3]);

        $this->processor->beginComponent($name4, $type2);
        $this->processor->beginTag("service", ["id" => $id4]);

        $expected = [
            \ilArtifactComponentDataDB::BY_TYPE_AND_NAME => [
                $type1 => [
                    $name1 => $id1,
                    $name2 => $id2
                ],
                $type2 => [
                    $name3 => $id3,
                    $name4 => $id4
                ],
            ],
            \ilArtifactComponentDataDB::BY_ID => [
                $id1 => [$type1, $name1],
                $id2 => [$type1, $name2],
                $id3 => [$type2, $name3],
                $id4 => [$type2, $name4]
            ]
        ];

        $this->assertEquals($expected, $this->processor->getData());
    }

    public function testTagComponentTypeMismatch() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        $type = "Services";
        $name = "NAME";
        $id = "ID";

        $this->processor->beginComponent($name, $type);
        $this->processor->beginTag("module", ["id" => $id]);
    }

    public function testMissingId() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        $type = "Services";
        $name = "NAME";

        $this->processor->beginComponent($name, $type);
        $this->processor->beginTag("service", []);
    }
}
