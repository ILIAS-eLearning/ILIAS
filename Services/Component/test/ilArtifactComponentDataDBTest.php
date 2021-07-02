<?php

/* Copyright (c) 2021 Richard Klees <richard.klees@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

class ilArtifactComponentDataDBTest extends TestCase
{
    public static array $component_data = [
        "mod1" => ["Modules", "Module1", [
            ["slt1", "Slot1"],
            ["slt2", "Slot2"],
        ]],
        "mod2" => ["Modules", "Module2", [
        ]],
        "ser1" => ["Services", "Service1", [
            ["slt3", "Slot3"]
        ]],
        "ser2" => ["Services", "Service2", [
            ["slt4", "Slot4"]
        ]]
    ];

    protected function setUp() : void
    {
        $this->db = new class() extends ilArtifactComponentDataDB {
            protected function readComponentData() : array
            {
                return ilArtifactComponentDataDBTest::$component_data;
            }
        };

        $slots1 = [];
        $this->mod1 = new ilComponentInfo(
            "mod1",
            "Modules",
            "Module1",
            $slots1
        );
        $this->slt1 = new ilPluginSlotInfo(
            $this->mod1,
            "slt1",
            "Slot1"
        );
        $this->slt2 = new ilPluginSlotInfo(
            $this->mod1,
            "slt2",
            "Slot2"
        );
        $slots1 = ["slt1" => $this->slt1, "slt2" => $this->slt2];

        $slots2 = [];
        $this->mod2 = new ilComponentInfo(
            "mod2",
            "Modules",
            "Module2",
            $slots2
        );

        $slots3 = [];
        $this->ser1 = new ilComponentInfo(
            "ser1",
            "Services",
            "Service1",
            $slots3
        );
        $this->slt3 = new ilPluginSlotInfo(
            $this->ser1,
            "slt3",
            "Slot3"
        );
        $slots3 = ["slt3" => $this->slt3];

        $slots4 = [];
        $this->ser2 = new ilComponentInfo(
            "ser2",
            "Services",
            "Service2",
            $slots4
        );
        $this->slt4 = new ilPluginSlotInfo(
            $this->ser2,
            "slt4",
            "Slot4"
        );
        $slots4 = ["slt4" => $this->slt4];
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

        $this->assertEquals($this->mod1, $result["mod1"]);
        $this->assertEquals($this->mod2, $result["mod2"]);
        $this->assertEquals($this->ser1, $result["ser1"]);
        $this->assertEquals($this->ser2, $result["ser2"]);
    }

    public function testGetComponentById()
    {
        $slots = [];
        $this->assertEquals($this->mod1, $this->db->getComponentById("mod1"));
        $this->assertEquals($this->mod2, $this->db->getComponentById("mod2"));
        $this->assertEquals($this->ser1, $this->db->getComponentById("ser1"));
        $this->assertEquals($this->ser2, $this->db->getComponentById("ser2"));
    }

    public function testGetComponentByTypeAndName()
    {
        $slots = [];
        $this->assertEquals($this->mod1, $this->db->getComponentByTypeAndName("Modules", "Module1"));
        $this->assertEquals($this->mod2, $this->db->getComponentByTypeAndName("Modules", "Module2"));
        $this->assertEquals($this->ser1, $this->db->getComponentByTypeAndName("Services", "Service1"));
        $this->assertEquals($this->ser2, $this->db->getComponentByTypeAndName("Services", "Service2"));
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

    public function testGetPluginSlots()
    {
        $slots = iterator_to_array($this->db->getPluginSlots());

        $ids = array_keys($slots);
        $expected_ids = ["slt1", "slt2", "slt3", "slt4"];
        sort($ids);
        sort($expected_ids);

        $this->assertEquals($expected_ids, $ids);

        $this->assertEquals($this->slt1, $slots["slt1"]);
        $this->assertEquals($this->slt2, $slots["slt2"]);
        $this->assertEquals($this->slt3, $slots["slt3"]);
        $this->assertEquals($this->slt4, $slots["slt4"]);
    }

    public function testGetPluginslotById()
    {
        $this->assertEquals($this->slt1, $this->db->getPluginslotById("slt1"));
        $this->assertEquals($this->slt2, $this->db->getPluginslotById("slt2"));
        $this->assertEquals($this->slt3, $this->db->getPluginslotById("slt3"));
        $this->assertEquals($this->slt4, $this->db->getPluginslotById("slt4"));
    }

    public function testNoPluginSlot()
    {
        $db = new class() extends ilArtifactComponentDataDB {
            protected function readComponentData() : array
            {
                return ["mod2" => ["Modules", "Module2", []]];
            }
        };

        $slots = iterator_to_array($db->getPluginSlots());
        $this->assertEquals([], $slots);
    }
}
