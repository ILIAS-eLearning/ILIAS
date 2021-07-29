<?php

/* Copyright (c) 2021 Richard Klees <richard.klees@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

class ilPluginSlotDefinitionProcessorTest extends TestCase
{
    protected ilDBInterface $db;
    protected ilPluginSlotDefinitionProcessor $processor;

    protected function setUp() : void
    {
        $this->db = $this->createMock(\ilDBInterface::class);
        $this->processor = new ilPluginSlotDefinitionProcessor($this->db);
    }

    public function testPurge() : void
    {
        $this->db->expects($this->once())
            ->method("manipulate")
            ->with("DELETE FROM il_pluginslot");

        $this->processor->purge();
    }

    public function testBeginTagPluginSlot() : void
    {
        $type = "Modules";
        $name = "NAME";
        $id = "ID";
        $slot_name = "SLOT_NAME";

        $this->db->expects($this->once())
            ->method("manipulateF")
            ->with(
                "INSERT INTO il_pluginslot (component, id, name) VALUES (%s, %s, %s)",
                ["text", "text", "text"],
                [$type . "/" . $name, $id, $slot_name]
            );

        $this->processor->beginComponent($name, $type);
        $this->processor->beginTag("pluginslot", ["id" => $id, "name" => $slot_name]);
    }

    public function testMissingId() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        $type = "Modules";
        $name = "NAME";
        $slot_name = "SLOT_NAME";

        $this->processor->beginComponent($name, $type);
        $this->processor->beginTag("pluginslot", ["name" => $slot_name]);
    }

    public function testMissingSlotName() : void
    {
        $this->expectException(\InvalidArgumentException::class);

        $type = "Modules";
        $name = "NAME";
        $id = "ID";

        $this->processor->beginComponent($name, $type);
        $this->processor->beginTag("pluginslot", ["id" => $id]);
    }
}
