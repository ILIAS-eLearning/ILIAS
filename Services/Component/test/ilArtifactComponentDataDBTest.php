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

    public function testGetComponentIds()
    {
        $expected = ["mod1", "mod2", "ser1", "ser2"];
        $result = iterator_to_array($this->db->getComponentIds());
        sort($expected);
        sort($result);
        $this->assertEquals($expected, $result);
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

    public function testGetComponentId()
    {
        $this->assertEquals("mod1", $this->db->getComponentId("Modules", "Module1"));
        $this->assertEquals("mod2", $this->db->getComponentId("Modules", "Module2"));
        $this->assertEquals("ser1", $this->db->getComponentId("Services", "Service1"));
        $this->assertEquals("ser2", $this->db->getComponentId("Services", "Service2"));
    }

    public function testGetComponentIdThrowsOnUnknownComponent1()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->db->getComponentId("Modules", "Module3");
    }

    public function testGetComponentIdThrowsOnUnknownComponent2()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->db->getComponentId("OtherComponent", "Service1");
    }

    public function testGetComponentType()
    {
        $this->assertEquals("Modules", $this->db->getComponentType("mod1"));
        $this->assertEquals("Modules", $this->db->getComponentType("mod2"));
        $this->assertEquals("Services", $this->db->getComponentType("ser1"));
        $this->assertEquals("Services", $this->db->getComponentType("ser2"));
    }

    public function testGetComponentTypeThrowsOnInvalidId()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->db->getComponentType("some_id");
    }

    public function testGetComponentName()
    {
        $this->assertEquals("Module1", $this->db->getComponentName("mod1"));
        $this->assertEquals("Module2", $this->db->getComponentName("mod2"));
        $this->assertEquals("Service1", $this->db->getComponentName("ser1"));
        $this->assertEquals("Service2", $this->db->getComponentName("ser2"));
    }

    public function testGetComponentNameThrowsOnInvalidId()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->db->getComponentName("some_id");
    }
}
