<?php

/* Copyright (c) 2021 Richard Klees <richard.klees@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

class ilArtifactComponentDataDBTest extends TestCase
{
    public static array $component_data = [
        ilArtifactComponentDataDB::BY_ID => [
            "mod1" => ["Modules", "Module1"],
            "mod2" => ["Modules", "Module2"],
            "ser1" => ["Services", "Service1"],
            "ser2" => ["Services", "Service2"],
        ],
        ilArtifactComponentDataDB::BY_TYPE_AND_NAME => [
            "Modules" => [
                "Module1" => "mod1",
                "Module2" => "mod2"
            ],
            "Services" => [
                "Service1" => "ser1",
                "Service2" => "ser2"
            ]
        ]
    ];

    protected function setUp() : void
    {
        $this->db = new class() extends ilArtifactComponentDataDB {
            public function __construct()
            {
                $this->component_data = ilArtifactComponentDataDBTest::$component_data;
            }
        };
    }

    public function testHasComponent()
    {
        $this->assertTrue($this->db->hasComponent("Modules", "Module1"));
        $this->assertTrue($this->db->hasComponent("Modules", "Module2"));
        $this->assertTrue($this->db->hasComponent("Services", "Service1"));
        $this->assertTrue($this->db->hasComponent("Services", "Service2"));
        $this->assertFalse($this->db->hasComponent("Modules", "Module3"));
        $this->assertFalse($this->db->hasComponent("Modules", "Module4"));
        $this->assertFalse($this->db->hasComponent("Services", "Service3"));
        $this->assertFalse($this->db->hasComponent("Services", "Service4"));
    }

    public function testHasComponentThrowsOnUnknownType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->db->hasComponent("OtherComponent", "Module1");
    }

    public function testHasComponentId()
    {
        $this->assertTrue($this->db->hasComponentId("mod1"));
        $this->assertTrue($this->db->hasComponentId("mod2"));
        $this->assertTrue($this->db->hasComponentId("ser1"));
        $this->assertTrue($this->db->hasComponentId("ser2"));
        $this->assertFalse($this->db->hasComponentId("mod3"));
        $this->assertFalse($this->db->hasComponentId("mod4"));
        $this->assertFalse($this->db->hasComponentId("ser3"));
        $this->assertFalse($this->db->hasComponentId("ser4"));
    }

    public function testGetComponents()
    {
        $result = iterator_to_array($this->db->getComponents());

        $ids = array_keys($result);
        $expected_ids = ["mod1", "mod2", "ser1", "ser2"];
        sort($ids);
        sort($expected_ids);

        $this->assertEquals($expected_ids, $ids);

        $this->assertEquals(new ilComponentInfo("mod1", "Modules", "Module1"), $result["mod1"]);
        $this->assertEquals(new ilComponentInfo("mod2", "Modules", "Module2"), $result["mod2"]);
        $this->assertEquals(new ilComponentInfo("ser1", "Services", "Service1"), $result["ser1"]);
        $this->assertEquals(new ilComponentInfo("ser2", "Services", "Service2"), $result["ser2"]);
    }

    public function testGetComponentById()
    {
        $this->assertEquals(new ilComponentInfo("mod1", "Modules", "Module1"), $this->db->getComponentById("mod1"));
        $this->assertEquals(new ilComponentInfo("mod2", "Modules", "Module2"), $this->db->getComponentById("mod2"));
        $this->assertEquals(new ilComponentInfo("ser1", "Services", "Service1"), $this->db->getComponentById("ser1"));
        $this->assertEquals(new ilComponentInfo("ser2", "Services", "Service2"), $this->db->getComponentById("ser2"));
    }

    public function testGetComponentByTypeAndName()
    {
        $this->assertEquals(new ilComponentInfo("mod1", "Modules", "Module1"), $this->db->getComponentByTypeAndName("Modules", "Module1"));
        $this->assertEquals(new ilComponentInfo("mod2", "Modules", "Module2"), $this->db->getComponentByTypeAndName("Modules", "Module2"));
        $this->assertEquals(new ilComponentInfo("ser1", "Services", "Service1"), $this->db->getComponentByTypeAndName("Services", "Service1"));
        $this->assertEquals(new ilComponentInfo("ser2", "Services", "Service2"), $this->db->getComponentByTypeAndName("Services", "Service2"));
    }

    public function testGetComponentByTypeAndNameThrowsOnUnknownComponent1()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->db->getComponentByTypeAndName("Modules", "Module3");
    }

    public function testGetComponentByTypeAndNameThrowsOnUnknownComponent2()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->db->getComponentByTypeAndName("OtherComponent", "Service1");
    }

    public function testGetComponentByIdTypeThrowsOnInvalidId()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->db->getComponentById("some_id");
    }
}
