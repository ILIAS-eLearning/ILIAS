<?php

/* Copyright (c) 2021 Richard Klees <richard.klees@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

class ilComponentDefinitionInfoProcessorTest extends TestCase
{
    protected ilDBInterface $db;
    protected ilComponentDefinitionInfoProcessor $processor1;

    protected function setUp() : void
    {
        $this->db = $this->createMock(\ilDBInterface::class);
        $this->processor = new ilComponentInfoDefinitionProcessor($this->db);
    }

    public function testPurge() : void
    {
        $this->db->expects($this->once())
            ->method("manipulate")
            ->with("DELETE FROM il_component");

        $this->processor->purge();
    }

    public function testBeginTagModule() : void
    {
        $type = "Modules";
        $name = "NAME";
        $id = "ID";

        $this->db->expects($this->once())
            ->method("manipulateF")
            ->with(
                "INSERT INTO il_component (type, name, id) VALUES (%s,%s,%s)",
                ["text", "text", "text"],
                [$type, $name, $id]
            );

        $this->processor->beginComponent($name, $type);
        $this->processor->beginTag("module", ["id" => $id]);
    }

    public function testBeginTagService() : void
    {
        $type = "Services";
        $name = "NAME";
        $id = "ID";

        $this->db->expects($this->once())
            ->method("manipulateF")
            ->with(
                "INSERT INTO il_component (type, name, id) VALUES (%s,%s,%s)",
                ["text", "text", "text"],
                [$type, $name, $id]
            );

        $this->processor->beginComponent($name, $type);
        $this->processor->beginTag("service", ["id" => $id]);
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
